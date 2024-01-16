<?php

namespace App\Http\Services;

use Exception;
use Kreait\Firebase\Contract\Database;

class RealtimeDatabaseService
{
    private $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function storeMessage($adminId, $adminName, $customerId, $customerName, $content, $imagePath, $isAdmin)
    {
        $timestamp = time();
        $message = [
            'content' => $content,
            'image_path' => $imagePath,
            'is_admin' => (bool)$isAdmin,
            'admin_name' => $adminName,
            'is_read' => false,
            'aid' => $adminId, // To track which admin replys the message
            'created_at' => $timestamp,
        ];

        $adminReference = $this->database->getReference("admins/$adminId");
        $adminSnapshot = $adminReference->getSnapshot();

        // Ensure admin exists, create if not
        if (!$adminSnapshot->exists()) {
            $adminReference->set([
                'customers' => [
                    $customerId => [
                        'name' => $customerName,
                        'last_message_at' => $timestamp,
                        'messages' => [$message],
                    ],
                ],
            ]);

            return $message;
        }

        // Ensure customer exists, create if not
        $customerSnapshot = $adminSnapshot->getChild("customers/$customerId");
        $customerReference = $customerSnapshot->getReference();

        if (!$customerSnapshot->exists()) {
            $customerReference->set([
                'name' => $customerName,
                'last_message_at' => $timestamp,
                'messages' => [$message],
            ]);

            return $message;
        }

        // Insert new message into the messages list
        $messagesReference = $customerSnapshot->getChild("messages")->getReference();
        $messagesReference->push($message);

        // Update customer name, last_message_at,...
        $updateData = [
            'last_message_at' => $timestamp,
        ];

        if (!$isAdmin && $customerSnapshot->getChild('name')->getValue() !== $customerName) {
            $updateData['name'] = $customerName;
        }

        $customerReference->update($updateData);

        return $message;
    }

    public function updateAdminName($aid, $name)
    {
        $reference = $this->database->getReference("admins");
        $snapshot = $reference->getSnapshot();

        if ($snapshot->exists()) {
            $admins = $snapshot->getValue();

            foreach ($admins as $adminId => $admin) {
                if (!empty($admin['customers'])) {
                    foreach ($admin['customers'] as $customerId => $customer) {
                        if (!empty($customer['messages'])) {
                            foreach ($customer['messages'] as $key => $message) {
                                $messageAid = $message['aid'] ?? null;

                                // Do not update previous admin name
                                if (!empty($messageAid) && $messageAid == $aid) {
                                    $updatePath = "admins/$adminId/customers/$customerId/messages/$key/admin_name";

                                    $this->database->getReference()->update([
                                        $updatePath => $name
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        }

        return [];
    }

    public function updateReadStatus($adminId, $customerId, $targetRole)
    {
        $path = "admins/$adminId/customers/$customerId/messages";
        $reference = $this->database->getReference($path);

        if (!$reference->getSnapshot()->exists()) {
            throw new Exception('Messages reference not found.');
        }

        $messages = $reference->getValue();
        $updatePaths = [];

        foreach ($messages as $key => $message) {
            $updatePath = "$path/$key/is_read";

            if ($targetRole === 'admin' && !$message['is_read'] && $message['is_admin']) {
                $updatePaths[$updatePath] = true;
            } else if ($targetRole === 'customer' && !$message['is_read'] && !$message['is_admin']) {
                $updatePaths[$updatePath] = true;
            }
        }

        if (!empty($updatePaths)) {
            $this->database->getReference()->update($updatePaths);
        }

        return [];
    }

    public function migrateCustomerMessages($oldAdminId, $newAdminId, $customerId)
    {
        // Find customer chat history
        $oldReference = $this->database->getReference("admins/$oldAdminId/customers/$customerId");
        $snapshot = $oldReference->getSnapshot();

        if (!$snapshot->exists()) {
            return;
        }

        $data = $snapshot->getValue();

        // Migrate customer chat history
        $newReference = $this->database->getReference("admins/$newAdminId/customers/$customerId");

        $newReference->set($data);

        $oldReference->remove();

        return;
    }
}

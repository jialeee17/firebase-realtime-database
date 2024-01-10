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

    public function storeMessage($adminId, $customerId, $customerName, $content, $imagePath, $isAdmin)
    {
        $timestamp = time();
        $message = [
            'content' => $content,
            'image_path' => $imagePath,
            'is_admin' => (bool)$isAdmin,
            'is_read' => false,
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
                        'is_migrated' => false,
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
                'is_migrated' => false,
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

        if (!$isAdmin) {
            $updateData['name'] = $customerName;
        }

        $customerReference->update($updateData);

        return $message;
    }

    public function updateReadStatus($adminId, $customerId, $targetRole)
    {
        $reference = $this->database->getReference("admins/$adminId/customers/$customerId/messages");

        if ($reference->getSnapshot()->exists()) {
            $messages = $reference->getValue();

            foreach ($messages as $key => $message) {
                $updatePath = "admins/$adminId/customers/$customerId/messages/$key/is_read";

                if ($targetRole === 'admin' && $message['is_admin'] === true) {
                    $this->database->getReference()->update([
                        $updatePath => true
                    ]);
                } else if ($targetRole === 'customer' && $message['is_admin'] === false) {
                    $this->database->getReference()->update([
                        $updatePath => true
                    ]);
                }
            }
        }

        return [];
    }

    public function deleteChat($adminId, $customerId)
    {
        return $this->adminRepository->deleteChat($adminId, $customerId);
    }
}

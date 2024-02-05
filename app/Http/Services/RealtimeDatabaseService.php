<?php

namespace App\Http\Services;

use App\Models\Hap2py\CmsUser;
use App\Models\Hap2py\SystemSetting;
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
        $currentTimestamp = time();
        $message = [
            'content' => $content,
            'image_path' => $imagePath,
            'is_admin' => (bool)$isAdmin,
            'admin_name' => $adminName,
            'is_read' => false,
            'aid' => $adminId, // To track which admin replys the message
            'created_at' => $currentTimestamp,
        ];

        $customerReference = $this->database->getReference("admins/$adminId/customers/$customerId");
        $customerSnapshot = $customerReference->getSnapshot();

        // Insert new message into the messages list
        $messagesReference = $customerSnapshot->getChild("messages")->getReference();
        $messagesReference->push($message);

        // Update customer name, last_message_at,...
        $updateData = ['last_message_at' => $currentTimestamp];

        if (!$isAdmin && $customerSnapshot->getChild('name')->getValue() !== $customerName) {
            $updateData['name'] = $customerName;
        }

        $customerReference->update($updateData);

        // Auto Reply Message
        if (!$isAdmin) {
            $cmsUser = CmsUser::with('chatStatus')->where('id', $adminId)->first();

            if (empty($cmsUser)) {
                return;
            }

            $isOutOfBusinessHour = optional(SystemSetting::where('name', 'Out of Business Hour')->first())->value;
            $chatStatus = $cmsUser->chatStatus;

            if ($isOutOfBusinessHour == 1) {
                $autoReplyContent  = optional($chatStatus->where('name', 'Out of Business Hour')->first())->auto_reply_msg;
            } elseif ($chatStatus->name !== 'Online') {
                $autoReplyContent = $chatStatus->auto_reply_msg;
            }

            $autoReplyMessage = [
                'content' => $autoReplyContent,
                'is_admin' => true,
                'admin_name' => $adminName,
                'is_read' => false,
                'aid' => $adminId, // To track which admin replys the message
                'created_at' => strtotime('+1 second', $currentTimestamp),
            ];

            sleep(1);
            $messagesReference->push($autoReplyMessage);
        }

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

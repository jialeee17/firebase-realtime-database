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
            'is_admin' => $isAdmin ? true : false,
            'is_read' => false,
            'created_at' => $timestamp,
        ];

        $adminsRef = $this->database->getReference('admins');
        $adminRef = $adminsRef->getChild($adminId);

        // Ensure admin exists, create if not
        if (!$adminRef->getSnapshot()->exists()) {
            $adminsRef->getChild($adminId)->set([
                'customers' => [
                    $customerId => [
                        'name' => $customerName,
                        'messages' => [$message],
                        'last_message_at' => $timestamp
                    ],
                ],
            ]);

            return;
        }

        $customersRef = $adminRef->getChild("customers");
        $customerRef = $customersRef->getChild($customerId);

        // Ensure customer exists, create if not
        if (!$customerRef->getSnapshot()->exists()) {
            $customersRef->getChild($customerId)->set([
                'name' => $customerName,
                'messages' => [$message],
                'last_message_at' => $timestamp
            ]);

            return;
        }

        // Push the message
        $messagesRef = $customerRef->getChild("messages");
        $messagesRef->push($message);

        // Update customer name
        if (!$isAdmin) {
            $customersRef->getChild($customerId)->update([
                'name' => $customerName,
            ]);
        }

        $customersRef->getChild($customerId)->update([
            'last_message_at' => $timestamp
        ]);

        return $message;
    }
    }

    public function deleteChat($adminId, $customerId)
    {
        return $this->adminRepository->deleteChat($adminId, $customerId);
    }
}

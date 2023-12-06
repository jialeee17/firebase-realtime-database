<?php

namespace App\Repositories;

use Kreait\Firebase\Contract\Database;
use App\Interfaces\AdminRepositoryInterface;
use Exception;

class AdminRepository implements AdminRepositoryInterface
{
    private $database;
    private $table = 'admins';

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function storeMessage($adminId, $customerId, $message)
    {
        $message['is_read'] = false;
        $message['created_at'] = time();

        $adminsRef = $this->database->getReference($this->table);
        $adminRef = $adminsRef->getChild("admin_id_{$adminId}");

        // Ensure admin exists, create if not
        if (!$adminRef->getSnapshot()->exists()) {
            $adminsRef->getChild("admin_id_{$adminId}")->set([
                'customers' => [
                    "customer_id_{$customerId}" => [
                        'messages' => [$message]
                    ],
                ],
            ]);

            return;
        }

        $customersRef = $adminRef->getChild("customers");
        $customerRef = $customersRef->getChild("customer_id_{$customerId}");

        // Ensure customer exists, create if not
        if (!$customerRef->getSnapshot()->exists()) {
            $customersRef->getChild("customer_id_{$customerId}")->set([
                'messages' => [$message]
            ]);

            return;
        }

        // Push the message
        $messagesRef = $customerRef->getChild("messages");
        $messagesRef->push($message);

        return;
    }

    public function deleteChat($adminId, $customerId)
    {
        $adminsRef = $this->database->getReference($this->table);

        $chatRef = $adminsRef->getChild("admin_id_{$adminId}/customers/customer_id_{$customerId}");

        if (!$chatRef->getSnapshot()->exists()) {
            throw new Exception('Chat reference not found!');
        }

        $chatRef->remove();

        return;
    }
}

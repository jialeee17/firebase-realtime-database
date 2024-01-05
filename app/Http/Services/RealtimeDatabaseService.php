<?php

namespace App\Http\Services;

use Carbon\Carbon;
use App\Repositories\AdminRepository;
use Kreait\Firebase\Contract\Database;

class RealtimeDatabaseService
{
    private $database;
    private $adminRepository;

    public function __construct(Database $database, AdminRepository $adminRepository)
    {
        $this->database = $database;
        $this->adminRepository = $adminRepository;
    }

    public function storeMessage($adminId, $customerId, $customerName, $content, $imagePath, $isAdmin)
    {
        $message = [
            'content' => $content,
            'image_path' => $imagePath,
            'is_admin' => $isAdmin ? true : false,
            'created_at' => time(),
        ];

        $adminsRef = $this->database->getReference('admins');
        $adminRef = $adminsRef->getChild($adminId);

        // Ensure admin exists, create if not
        if (!$adminRef->getSnapshot()->exists()) {
            $adminsRef->getChild($adminId)->set([
                'customers' => [
                    $customerId => [
                        'name' => $customerName,
                        'messages' => [$message]
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
                'messages' => [$message]
            ]);

            return;
        }

        // Push the message
        $messagesRef = $customerRef->getChild("messages");
        $messagesRef->push($message);

        // Update customer name
        $customersRef->getChild($customerId)->update(['name' => $customerName]);

        return;
    }

    public function deleteChat($adminId, $customerId)
    {
        return $this->adminRepository->deleteChat($adminId, $customerId);
    }
}

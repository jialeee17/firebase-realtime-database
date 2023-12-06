<?php

namespace App\Http\Services;

use Carbon\Carbon;
use App\Repositories\AdminRepository;

class RealtimeDatabaseService
{
    private $adminRepository;

    public function __construct(AdminRepository $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    public function storeMessage($adminId, $customerId, $message)
    {
        return $this->adminRepository->storeMessage($adminId, $customerId, $message);
    }

    public function deleteChat($adminId, $customerId)
    {
        return $this->adminRepository->deleteChat($adminId, $customerId);
    }
}

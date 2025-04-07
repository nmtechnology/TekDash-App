<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkOrder;

class WorkOrderPolicy
{
    public function before(User $user)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, WorkOrder $workOrder)
    {
        return true;
    }

    public function create(User $user)
    {
        return $user->isAdmin();
    }

    public function update(User $user, WorkOrder $workOrder)
    {
        return $user->isAdmin() || $user->isTech();
    }

    public function updateStatus(User $user, WorkOrder $workOrder)
    {
        return $user->isAdmin() || $user->isTech();
    }

    public function delete(User $user, WorkOrder $workOrder)
    {
        return $user->isAdmin();
    }

    public function archive(User $user, WorkOrder $workOrder)
    {
        return $user->isAdmin();
    }

    public function invoice(User $user, WorkOrder $workOrder)
    {
        return $user->isAdmin();
    }

    public function addNotes(User $user, WorkOrder $workOrder)
    {
        return true;
    }

    public function uploadImages(User $user, WorkOrder $workOrder)
    {
        return true;
    }

    public function getSignature(User $user, WorkOrder $workOrder)
    {
        return true;
    }
}

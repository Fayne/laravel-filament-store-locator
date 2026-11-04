<?php

namespace Fayne\StoreLocator\Policies;

use Fayne\CMS\Models\Admin;

class StorePolicy
{
    public function before($admin, string $ability, $record = null): ?bool
    {
        if ($record instanceof \Illuminate\Database\Eloquent\Model
            && (app(\Fayne\CMS\Support\TenantContext::class)->id() === null
                || (string) $record->getAttribute('tenant_id') !== (string) app(\Fayne\CMS\Support\TenantContext::class)->id())) {
            return false;
        }
        return null;
    }

    public function viewAny(Admin $admin): bool
    {
        return $this->allows($admin, 'tenant.viewAnyStore');
    }

    public function view(Admin $admin): bool
    {
        return $this->allows($admin, 'tenant.viewStore');
    }

    public function create(Admin $admin): bool
    {
        return $this->allows($admin, 'tenant.createStore');
    }

    public function update(Admin $admin): bool
    {
        return $this->allows($admin, 'tenant.updateStore');
    }

    public function delete(Admin $admin): bool
    {
        return $this->allows($admin, 'tenant.deleteStore');
    }

    private function allows(Admin $admin, string $permission): bool
    {
        return $admin->is_active && \Fayne\CMS\Support\Authorization\Authorization::allows($admin, $permission);
    }
}

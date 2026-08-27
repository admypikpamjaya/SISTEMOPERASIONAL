<?php

namespace Tests\Unit\AccessControl;

use App\Enums\User\UserRole;
use App\Http\Controllers\Admin\BlastController;
use Tests\TestCase;

class BlastingWhatsappDeviceAccessTest extends TestCase
{
    public function test_manage_phone_menu_is_available_for_blasting_role(): void
    {
        $managePhoneMenu = collect(config('menu'))
            ->flatMap(fn (array $menu): array => $menu['children'] ?? [])
            ->firstWhere('route', 'admin.blast.whatsapp.manage');

        $this->assertNotNull($managePhoneMenu);
        $this->assertContains(UserRole::BLASTING->value, $managePhoneMenu['roles'] ?? []);
    }

    public function test_blasting_can_control_whatsapp_devices_without_becoming_system_operator(): void
    {
        $controller = new BlastController();
        $user = (object) ['role' => UserRole::BLASTING->value];

        $deviceAccess = new \ReflectionMethod($controller, 'hasWhatsappDeviceControlAccess');
        $deviceAccess->setAccessible(true);

        $systemAccess = new \ReflectionMethod($controller, 'hasSystemOperatorAccess');
        $systemAccess->setAccessible(true);

        $this->assertTrue($deviceAccess->invoke($controller, $user));
        $this->assertFalse($systemAccess->invoke($controller, $user));
    }
}

<?php

namespace Tests\Feature;

use App\Filament\Agent\Pages\ChangePassword;
use App\Models\Agent;
use App\Models\AgentProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class AgentChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_can_change_only_their_own_password_and_an_audit_event_is_recorded(): void
    {
        [$user, $agent] = $this->agentUser();
        [$otherUser] = $this->agentUser('KF-OTHER');
        $otherPassword = $otherUser->password;

        $this->actingAs($user);

        Livewire::test(ChangePassword::class)
            ->set('data.current_password', 'password')
            ->set('data.password', 'A-new-secure-password-42')
            ->set('data.password_confirmation', 'A-new-secure-password-42')
            ->call('changePassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('A-new-secure-password-42', $user->fresh()->password));
        $this->assertFalse(Hash::check('password', $user->fresh()->password));
        $this->assertSame($otherPassword, $otherUser->fresh()->password);

        $activity = Activity::query()->where('description', 'Agent changed password')->latest('id')->first();
        $this->assertNotNull($activity);
        $this->assertSame($user->id, $activity->causer_id);
        $this->assertSame($user->id, $activity->subject_id);
        $this->assertSame($agent->id, $activity->properties->get('agent_id'));
        $this->assertArrayNotHasKey('password', $activity->properties->toArray());
    }

    public function test_wrong_current_password_and_confirmation_mismatch_are_rejected(): void
    {
        [$user] = $this->agentUser();
        $originalHash = $user->password;
        $this->actingAs($user);

        Livewire::test(ChangePassword::class)
            ->set('data.current_password', 'incorrect-password')
            ->set('data.password', 'A-new-secure-password-42')
            ->set('data.password_confirmation', 'A-new-secure-password-42')
            ->call('changePassword')
            ->assertHasErrors(['data.current_password']);

        Livewire::test(ChangePassword::class)
            ->set('data.current_password', 'password')
            ->set('data.password', 'A-new-secure-password-42')
            ->set('data.password_confirmation', 'does-not-match')
            ->call('changePassword')
            ->assertHasErrors(['data.password']);

        $this->assertSame($originalHash, $user->fresh()->password);
    }

    public function test_existing_password_security_rule_and_password_reuse_check_are_enforced(): void
    {
        [$user] = $this->agentUser();
        $this->actingAs($user);

        Livewire::test(ChangePassword::class)
            ->set('data.current_password', 'password')
            ->set('data.password', 'short')
            ->set('data.password_confirmation', 'short')
            ->call('changePassword')
            ->assertHasErrors(['data.password']);

        Livewire::test(ChangePassword::class)
            ->set('data.current_password', 'password')
            ->set('data.password', 'password')
            ->set('data.password_confirmation', 'password')
            ->call('changePassword')
            ->assertHasErrors(['data.password']);

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    private function agentUser(string $code = 'KF-AGENT'): array
    {
        $program = AgentProgram::create([
            'name' => 'Program '.uniqid(),
            'commission_rate' => '10.0000',
            'commission_duration_months' => 12,
            'holding_period_days' => 7,
            'is_active' => true,
        ]);
        $user = User::factory()->create([
            'password' => 'password',
            'is_active' => true,
        ]);
        $agent = Agent::create([
            'user_id' => $user->id,
            'agent_program_id' => $program->id,
            'referral_code' => $code,
            'status' => Agent::STATUS_ACTIVE,
            'activated_at' => now(),
        ]);

        return [$user, $agent];
    }
}

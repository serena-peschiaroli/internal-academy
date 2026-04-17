<?php

use App\Models\Registration;
use App\Models\Role;
use App\Models\User;
use App\Policies\RegistrationPolicy;
use App\RoleType;

/**
 * Build a User with the given role and optional id without hitting the database.
 */
function makeRegistrationPolicyUser(RoleType $roleType, int $id = 1): User
{
    $role = new Role;
    $role->forceFill(['key' => $roleType->value]);

    $user = new User;
    $user->forceFill(['id' => $id]);
    $user->setRelation('role', $role);

    return $user;
}

// ---------------------------------------------------------------------------
// viewAny — both roles can list registrations
// ---------------------------------------------------------------------------

test('RegistrationPolicy viewAny allows admin', function () {
    $policy = new RegistrationPolicy;
    $admin = makeRegistrationPolicyUser(RoleType::ADMIN);

    expect($policy->viewAny($admin))->toBeTrue();
});

test('RegistrationPolicy viewAny allows employee', function () {
    $policy = new RegistrationPolicy;
    $employee = makeRegistrationPolicyUser(RoleType::EMPLOYEE);

    expect($policy->viewAny($employee))->toBeTrue();
});

// ---------------------------------------------------------------------------
// view — admin sees any; employee sees only own
// ---------------------------------------------------------------------------

test('RegistrationPolicy view allows admin to see any registration', function () {
    $policy = new RegistrationPolicy;
    $admin = makeRegistrationPolicyUser(RoleType::ADMIN, id: 1);

    $registration = new Registration;
    $registration->forceFill(['user_id' => 99]); // belongs to another user

    expect($policy->view($admin, $registration))->toBeTrue();
});

test('RegistrationPolicy view allows employee to see own registration', function () {
    $policy = new RegistrationPolicy;
    $employee = makeRegistrationPolicyUser(RoleType::EMPLOYEE, id: 5);

    $registration = new Registration;
    $registration->forceFill(['user_id' => 5]);

    expect($policy->view($employee, $registration))->toBeTrue();
});

test('RegistrationPolicy view denies employee viewing another employee registration', function () {
    $policy = new RegistrationPolicy;
    $employee = makeRegistrationPolicyUser(RoleType::EMPLOYEE, id: 5);

    $registration = new Registration;
    $registration->forceFill(['user_id' => 99]);

    expect($policy->view($employee, $registration))->toBeFalse();
});

// ---------------------------------------------------------------------------
// create — employee only
// ---------------------------------------------------------------------------

test('RegistrationPolicy create allows employee', function () {
    $policy = new RegistrationPolicy;
    $employee = makeRegistrationPolicyUser(RoleType::EMPLOYEE);

    expect($policy->create($employee))->toBeTrue();
});

test('RegistrationPolicy create denies admin', function () {
    $policy = new RegistrationPolicy;
    $admin = makeRegistrationPolicyUser(RoleType::ADMIN);

    expect($policy->create($admin))->toBeFalse();
});

// ---------------------------------------------------------------------------
// delete — admin can cancel any registration; employee only own
// ---------------------------------------------------------------------------

test('RegistrationPolicy delete allows admin to cancel any registration', function () {
    $policy = new RegistrationPolicy;
    $admin = makeRegistrationPolicyUser(RoleType::ADMIN, id: 1);

    $registration = new Registration;
    $registration->forceFill(['user_id' => 99]); // belongs to a different user

    // Edge case: admin should be able to cancel registrations they did not create.
    expect($policy->delete($admin, $registration))->toBeTrue();
});

test('RegistrationPolicy delete allows employee to cancel own registration', function () {
    $policy = new RegistrationPolicy;
    $employee = makeRegistrationPolicyUser(RoleType::EMPLOYEE, id: 7);

    $registration = new Registration;
    $registration->forceFill(['user_id' => 7]);

    expect($policy->delete($employee, $registration))->toBeTrue();
});

test('RegistrationPolicy delete denies employee cancelling another employee registration', function () {
    $policy = new RegistrationPolicy;
    $employee = makeRegistrationPolicyUser(RoleType::EMPLOYEE, id: 7);

    $registration = new Registration;
    $registration->forceFill(['user_id' => 99]);

    expect($policy->delete($employee, $registration))->toBeFalse();
});

<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Workshop;
use App\Policies\WorkshopPolicy;
use App\RoleType;

/**
 * Build a User with the given role without hitting the database.
 * Relations are injected directly via setRelation so no query is issued.
 */
function makeWorkshopPolicyUser(RoleType $roleType): User
{
    $role = new Role;
    $role->forceFill(['key' => $roleType->value]);

    $user = new User;
    $user->setRelation('role', $role);

    return $user;
}

// ---------------------------------------------------------------------------
// viewAny — both roles can list workshops
// ---------------------------------------------------------------------------

test('WorkshopPolicy viewAny allows admin', function () {
    $policy = new WorkshopPolicy;
    $admin = makeWorkshopPolicyUser(RoleType::ADMIN);

    expect($policy->viewAny($admin))->toBeTrue();
});

test('WorkshopPolicy viewAny allows employee', function () {
    $policy = new WorkshopPolicy;
    $employee = makeWorkshopPolicyUser(RoleType::EMPLOYEE);

    expect($policy->viewAny($employee))->toBeTrue();
});

// ---------------------------------------------------------------------------
// view — delegates to viewAny, same expectations
// ---------------------------------------------------------------------------

test('WorkshopPolicy view allows admin', function () {
    $policy = new WorkshopPolicy;
    $admin = makeWorkshopPolicyUser(RoleType::ADMIN);
    $workshop = new Workshop;

    expect($policy->view($admin, $workshop))->toBeTrue();
});

test('WorkshopPolicy view allows employee', function () {
    $policy = new WorkshopPolicy;
    $employee = makeWorkshopPolicyUser(RoleType::EMPLOYEE);
    $workshop = new Workshop;

    expect($policy->view($employee, $workshop))->toBeTrue();
});

// ---------------------------------------------------------------------------
// create — admin only
// ---------------------------------------------------------------------------

test('WorkshopPolicy create allows admin', function () {
    $policy = new WorkshopPolicy;
    $admin = makeWorkshopPolicyUser(RoleType::ADMIN);

    expect($policy->create($admin))->toBeTrue();
});

test('WorkshopPolicy create denies employee', function () {
    $policy = new WorkshopPolicy;
    $employee = makeWorkshopPolicyUser(RoleType::EMPLOYEE);

    expect($policy->create($employee))->toBeFalse();
});

// ---------------------------------------------------------------------------
// update — admin only
// ---------------------------------------------------------------------------

test('WorkshopPolicy update allows admin', function () {
    $policy = new WorkshopPolicy;
    $admin = makeWorkshopPolicyUser(RoleType::ADMIN);
    $workshop = new Workshop;

    expect($policy->update($admin, $workshop))->toBeTrue();
});

test('WorkshopPolicy update denies employee', function () {
    $policy = new WorkshopPolicy;
    $employee = makeWorkshopPolicyUser(RoleType::EMPLOYEE);
    $workshop = new Workshop;

    expect($policy->update($employee, $workshop))->toBeFalse();
});

// ---------------------------------------------------------------------------
// delete — admin only
// ---------------------------------------------------------------------------

test('WorkshopPolicy delete allows admin', function () {
    $policy = new WorkshopPolicy;
    $admin = makeWorkshopPolicyUser(RoleType::ADMIN);
    $workshop = new Workshop;

    expect($policy->delete($admin, $workshop))->toBeTrue();
});

test('WorkshopPolicy delete denies employee', function () {
    $policy = new WorkshopPolicy;
    $employee = makeWorkshopPolicyUser(RoleType::EMPLOYEE);
    $workshop = new Workshop;

    expect($policy->delete($employee, $workshop))->toBeFalse();
});

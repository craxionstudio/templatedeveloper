<?php

use App\Models\User;

it('mengarahkan tamu ke halaman login admin', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('menampilkan halaman login admin', function () {
    $this->get('/admin/login')->assertOk();
});

it('mengizinkan admin membuka dashboard', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk();
});

it('membuat akun admin lewat seeder', function () {
    $this->seed();

    expect(User::where('email', 'admin@example.com')->exists())->toBeTrue();
});

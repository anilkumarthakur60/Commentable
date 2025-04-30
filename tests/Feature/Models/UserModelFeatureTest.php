<?php

use Anil\Comments\Tests\TestSetup\Models\UserModel;
use Illuminate\Support\Facades\Hash;

describe('Testing Commentable on UserModel', function () {
    it('can create a user', function () {
        $user = UserModel::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        expect($user)->toBeInstanceOf(UserModel::class);
        expect($user->name)->toBe('Test User');
    });
});

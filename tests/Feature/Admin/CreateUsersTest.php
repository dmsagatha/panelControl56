<?php

namespace Tests\Feature\Admin;

use App\Models\{User, Profession, Skill};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateUsersTest extends TestCase
{
  use RefreshDatabase;

  protected $defaultData = [
    'name'  => 'Super Admin',
    'email' => 'superadmin@admin.net',
    'password' => 'superadmin',
    'bio'      => 'Programador de Laravel y Vue.js',
    'twitter'  => 'https://twitter.com/superadmin',
    'profession_id' => '',
    'role' => 'user',
  ];
  
  /** @test */
  function it_loads_the_new_users_page()
  {
    $profession = factory(Profession::class)->create();

    $skillA = factory(Skill::class)->create();
    $skillB = factory(Skill::class)->create();
    
    $this->get('/usuarios/nuevo')
        ->assertStatus(200)
        ->assertSee('Crear usuario')
        /* ->assertViewHas('professions', function ($professions) use ($profession) {
            return $professions->contains($profession);
        })
        ->assertViewHas('skills', function ($skills) use ($skillA, $skillB) {
            return $skills->contains($skillA) && $skills->contains($skillB);
        }) */
      ;
  }

  /** @test */
  function it_creates_a_new_user()
  {
    $profession = factory(Profession::class)->create();

    $skillA = factory(Skill::class)->create();
    $skillB = factory(Skill::class)->create();
    $skillC = factory(Skill::class)->create();

    $this->post('/usuarios/', $this->withData([
      'skills' => [$skillA->id, $skillB->id],
      'profession_id' => $profession->id,
    ]))->assertRedirect('usuarios');

    // dd(User::first());  // Revisar el campo profession_id

    $this->assertCredentials([
      'name'  => 'Super Admin',
      'email' => 'superadmin@admin.net',
      'password' => 'superadmin',
      'role' => 'user',
    ]);

    $user = User::findByEmail('superadmin@admin.net');

    $this->assertDatabaseHas('user_profiles', [
        'bio' => 'Programador de Laravel y Vue.js',
        'twitter' => 'https://twitter.com/superadmin',
        'user_id' =>$user->id,
        'profession_id' => $profession->id,
    ]);

    $this->assertDatabaseHas('user_skill', [
        'user_id'  => $user->id,
        'skill_id' => $skillA->id,
    ]);

    $this->assertDatabaseHas('user_skill', [
        'user_id'  => $user->id,
        'skill_id' => $skillB->id,
    ]);

    $this->assertDatabaseMissing('user_skill', [
        'user_id'  => $user->id,
        'skill_id' => $skillC->id,
    ]);
  }

  /** @test */
  function the_twitter_field_is_optional()
  {
    $this->post('/usuarios/', $this->withData([
        'twitter' => null,
    ]))->assertRedirect('usuarios');

    $this->assertCredentials([
        'name' => 'Super Admin',
        'email' => 'superadmin@admin.net',
        'password' => 'superadmin',
    ]);

    $this->assertDatabaseHas('user_profiles', [
        'bio' => 'Programador de Laravel y Vue.js',
        'twitter' => null,
        'user_id' => User::findByEmail('superadmin@admin.net')->id,
    ]);
  }

  /** @test */
  function the_role_field_is_optional()
  {
    $this->post('/usuarios/', $this->withData([
        'role' => null,
    ]))->assertRedirect('usuarios');

    $this->assertDatabaseHas('users', [
        'email' => 'superadmin@admin.net',
        'role'  => 'user',
    ]);
  }

  /** @test */
  function the_role_must_be_valid()
  {
    $this->handleValidationExceptions();

    $this->post('/usuarios/', $this->withData([
        'role' => 'invalid-role',
    ]))->assertSessionHasErrors('role');

    $this->assertDatabaseEmpty('users');
  }

  /** @test */
  function the_profession_id_field_is_optional()
  {
    $this->post('/usuarios/', $this->withData([
        'profession_id' => null,
    ]))->assertRedirect('usuarios');

    $this->assertCredentials([
        'name'  => 'Super Admin',
        'email' => 'superadmin@admin.net',
        'password' => 'superadmin',
    ]);

    $this->assertDatabaseHas('user_profiles', [
        'bio' => 'Programador de Laravel y Vue.js',
        'profession_id' => null,
        'user_id' => User::findByEmail('superadmin@admin.net')->id,
    ]);
  }

  /** @test */
  function the_user_is_redirected_to_the_previous_page_when_the_validation_fails()
  {
    $this->handleValidationExceptions();

    $this->post('/usuarios/', []);

    $this->assertDatabaseEmpty('users');
  }

  /** @test */
  function the_name_is_required()
  {
    $this->handleValidationExceptions();

    $this->post('/usuarios/', $this->withData([
          'name'  => '',
        ]))
        ->assertSessionHasErrors(['name']);

    //  Comprobar que el usuario no se creo
    $this->assertDatabaseEmpty('users');
    /* $this->assertDatabaseMissing('users', [
      'email' => 'superadmin@admin.net',
    ]); */
  }

  /** @test */
  function the_email_is_required()
  {
    $this->handleValidationExceptions();

    $this->post('/usuarios/', $this->withData([
          'email' => '',
        ]))
        ->assertSessionHasErrors(['email']);

    //  Comprobar que el usuario no se creo
    $this->assertDatabaseEmpty('users');
  }

  /** @test */
  function the_email_must_be_valid()
  {
    $this->handleValidationExceptions();
    
    $this->post('/usuarios/', $this->withData([
          'email' => 'correo-no-valido',
        ]))
        ->assertSessionHasErrors(['email']);

        $this->assertDatabaseEmpty('users');
  }

  /** @test */
  function the_email_must_be_unique()
  {
    $this->handleValidationExceptions();
    
    factory(User::class)->create([
        'email' => 'superadmin@admin.net'
    ]);

    $this->post('/usuarios/', $this->withData([
          'email' => 'superadmin@admin.net',
        ]))
        ->assertSessionHasErrors(['email']);

    $this->assertEquals(1, User::count());
  }

  /** @test */
  function the_password_is_required()
  {
    $this->handleValidationExceptions();
        
    $this->post('/usuarios/', $this->withData([
          'password' => null,
        ]))
        ->assertSessionHasErrors(['password']);

    //  Comprobar que el usuario no se creo
    $this->assertDatabaseEmpty('users');
  }

  /** @test */
  function the_profession_must_be_valid()
  {
    $this->handleValidationExceptions();

    $this->post('/usuarios/', $this->withData([
            'profession_id' => '999'
        ]))
        ->assertSessionHasErrors(['profession_id']);

    $this->assertDatabaseEmpty('users');
  }

  /** @test */
  function only_not_deleted_professions_can_be_selected()
  {
    $this->withExceptionHandling();

    $deletedProfession = factory(Profession::class)->create([
        'deleted_at' => now()->format('Y-m-d'),
    ]);

    $this->handleValidationExceptions();

    $this->post('/usuarios/', $this->withData([
            'profession_id' => $deletedProfession->id,
        ]))
        ->assertSessionHasErrors(['profession_id']);

    $this->assertDatabaseEmpty('users');
  }

  /** @test */
  function the_skills_must_be_an_array()
  {
    $this->handleValidationExceptions();

    $this->post('/usuarios/', $this->withData([
            'skills' => 'PHP, JS'
        ]))
        ->assertSessionHasErrors(['skills']);

    $this->assertDatabaseEmpty('users');
  }

  /** @test */
  function the_skills_must_be_valid()
  {
    $this->handleValidationExceptions();

    $skillA = factory(Skill::class)->create();
    $skillB = factory(Skill::class)->create();

    $this->post('/usuarios/', $this->withData([
            'skills' => [$skillA->id, $skillB->id + 1],
        ]))
        ->assertSessionHasErrors(['skills']);

    $this->assertDatabaseEmpty('users');
  }
}
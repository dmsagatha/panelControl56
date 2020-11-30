<?php

namespace App\Http\Controllers;

use App\Models\{User, Profession, Skill};
use App\Models\UserFilter;
use App\Http\Requests\{UserCreateRequest, UserUpdateRequest};
use Illuminate\Http\Request;

class UserController extends Controller
{
  public function index(Request $request, UserFilter $filters)
  {
    // 2.38 Creación de la clase QueryFilter - UserFilter
    $users = User::query()
        ->with('team', 'skills', 'profile.profession')
        ->filterBy($filters, $request->only(['state', 'role', 'search', 'skills']))
        ->orderByDesc('created_at')
        ->paginate()
        ->appends($filters->valid());

    return view('users.index', [
      'users'  => $users,
      'view'   => 'index',
      'skills' => Skill::orderBy('name')->get(),
      'checkedSkills' => collect(request('skills'))
    ]);
  }

  public function trashed()
  {
    $users = User::with('team', 'skills', 'profile.profession')
        ->onlyTrashed()
        ->paginate();

    return view('users.index', [
      'users' => $users,
      'view'  => 'trash',
    ]);
  }
  
  public function show(User $user)
  {
    return view('users.show', compact('user'));
  }

  public function create()
  {
    return $this->form('users.create', new User);
  }

  public function store(UserCreateRequest $request)
  {
    $request->createUser();
    
    return redirect()->route('users.index');
  }

  public function edit(User $user)
  {
    return $this->form('users.edit', $user);
  }

  protected function form($view, User $user)
  {
    return view($view, [
      'professions' => Profession::orderBy('title', 'ASC')->get(),
      'skills' => Skill::orderBy('name', 'ASC')->get(),
      'user'   => $user,
    ]);
  }
  
  /**
   * 2-17-Uso de Form Requests para validar la actualización de registros
   */
  public function update(UserUpdateRequest $request, User $user)
  {
    $request->updateUser($user);
    
    return redirect()->route('users.show', ['user' => $user]);
  }

  /** Elimnar el Usuario de forma lógica */
  public function trash(User $user)
  {
    $user->delete();
    $user->profile()->delete();

    return redirect()->route('users.index');
  }
  
  public function destroy($id)
  {
    $user = User::onlyTrashed()->whereId($id)->firstOrFail();

    $user->forceDelete();
    
    return redirect()->route('users.index')->with('status', 'El usuario fue eliminado con éxito!');
  }
}
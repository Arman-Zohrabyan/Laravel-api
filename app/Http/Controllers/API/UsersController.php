<?php 

namespace App\Http\Controllers\API;

// use App\Http\Requests\API\users\AddUserRequest;
// use App\Http\Requests\API\users\DeleteUserRequest;
// use App\Http\Requests\API\users\EditUserRequest;
// use App\Http\Requests\API\users\GetRequiredOptionsRequest;
// use App\Http\Requests\API\users\UploadImageUserRequest;
// use App\Http\Requests\API\users\GetListUserRequest;
// use App\Http\Requests\API\users\GetUserRequest;
// use App\Http\Requests\API\auth\LoginRequest;
// use App\Http\Requests\API\auth\RegisterRequest;

// use App\Models\Scope;
// use App\Models\User;
// use Illuminate\Http\Request;
// use Illuminate\Support\Str;


class UsersController extends APIController
{
  /**
   * List users
   * 
   * @param  GetListUserRequest $request
   * @return Response
   */
  public function getList(GetListUserRequest $request)
  {
    $query = User::query();

    // Check for expanders
    if ($request->hasExpanders()) {
      $query->with($request->getExpanders());
    }

    $query = $this->filter($query, $request);
    $query = $this->sort($query, $request);
    $totalPages = $this->countPages($query, $request);
    $query = $this->paginate($query, $request);
    $query->whereDoesntHave('blockedUser');

    return $this->respondPaginated($query->get(), $totalPages);
  }

    /**
     * Get User from specified id.
     *
     * @param  GetGroupRequest  $request
     * @param  int              $id
     * @return Response
     */
    public function get(GetUserRequest $request, $id)
    {
        if ($request->hasExpanders()) {
            $query = User::with($request->getExpanders());
        } else {
            $query = User::query();
        }
        return $this->respond($query->findOrFail($id));
    }

    /**
     * Current user
     *
     * @param  AddUserRequest $request
     * @return Response
     */
    public function add(AddUserRequest $request)
    {
        $newUser['first_name'] = $request->input("first_name");
        $newUser['last_name'] = $request->input("last_name");
        $newUser['email'] = $request->input("email");
        $newUser['team_member'] = $request->input('team_member');
        $newUser['password'] = bcrypt($request->input("password"));

        if($newUser['team_member'] == 1) {
            $newUser['visible'] = $request->input('visible');
            $newUser['position'] = $request->input('position');
            $newUser['description'] = $request->input('description');
        }

        $user = User::create($newUser);

        $scope = Scope::where('name', 'user')->first();

        $user->scopes()->attach($scope->id);
        if ($request->has('groups_ids')) {
            $groupsIds = explode(',', $request->input('groups_ids'));
            $user->groups()->sync($groupsIds);
        }

    if ($request->has('categories_ids')) {
      $categoriesIds = explode(',', $request->input('categories_ids'));
      $user->assignedCategories()->sync($categoriesIds);
    }

        return $this->respondCreated($user);
    }

    /**
     * Current user
     *
     * @param  EditUserRequest $request
     * @return Response
     */
    public function edit(EditUserRequest $request,$id)
    {
        $user = User::findOrFail($id);
        $editedUser = [];
        $editedUser['first_name'] = $request->input('first_name');
        $editedUser['last_name'] = $request->input('last_name');
        $editedUser['team_member'] = $request->input('team_member');
        $editedUser['email'] = $request->input('email');
        if ($request->has('password')){
            $editedUser['password'] = bcrypt($request->input('password'));
        }

        if($editedUser['team_member'] == 1) {
          $editedUser['visible'] = $request->input('visible');
          $editedUser['position'] = $request->input('position');
          $editedUser['description'] = $request->input('description');
    }

        $user->update($editedUser);
        $groupsIds=[];
        if ($request->has('groups_ids')) {
            $groupsIds = explode(',', $request->input('groups_ids'));
        }
    $user->groups()->sync($groupsIds);

    $categoriesIds=[];
        if ($request->has('categories_ids')) {
            $categoriesIds = explode(',', $request->input('categories_ids'));
        }
    $user->assignedCategories()->sync($categoriesIds);

        return $this->respondAccepted();
    }

    /**
     * Current user
     *
     * @param  DeleteUserRequest $request
     * @return Response
     */
    public function delete(DeleteUserRequest $request, $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return $this->respondAccepted();
    }

  /**
   * Create a new user account
   * 
   * @param Request $request 
   * @return Response
   */
  public function register()
  {
    dd("asdasd");
        // $newUser['first_name'] = $request->input("first_name");
        // $newUser['last_name'] = $request->input("last_name");
        // $newUser['email'] = $request->input("email");
        // $newUser['password'] = bcrypt($request->input("password"));

        // $user = User::create($newUser);

        // $scope = Scope::where('name', 'user')->first();

        // $user->scopes()->attach($scope->id);

        // return $this->respondCreated($user);
  }

    /**
     * Retrieve authenticated user.
     * 
     * @param  Request $request
     * @return Response
     */
    public function getUser(Request $request)
    {
      $user = User::getAuthenticated($request);
        if ($user){
            $user->imageURL = $user->image ? url("images/users/{$user->image}") : null;
        }

        return $this->respond([
            'user' => $user
        ]);
    }
    
  /**
   * Retrieve authentication token
   *
   * @param LoginRequest $request 
   * @return type
   */
  public function login(LoginRequest $request)
  {
    return $this->respond([
      'token' => User::login($request)
    ]);
  }

  /**
   * Invalidate current token
   *
   * @param Request $request
   */
  public function logout(Request $request)
  {
    User::logout($request);
    return $this->respondAccepted();
  }

  /**
   * Refresh an authentication token
   * 
   * @param Request $request 
   */
  public function refreshToken(Request $request)
  { 
    return $this->respond([
      'token' => User::refreshToken($request)
    ]);
  }

  /**
   * Get language for current Request
   * 
   * @param Request $request 
   */
  public function getLanguage(Request $request)
  {
    return $this->respond([
      'language' => 'en'
    ]);
  }

    /**
     * Upload user image
     *
     * @param  UploadImageUserRequest   $request
     * @param  int                          $id
     * @return Response
     */
  public function uploadImage(UploadImageUserRequest $request)
  {
    $user = User::getAuthenticated($request);
    if(!file_exists('images/users')){
      mkdir('images/users');
    }
        $request->file->move('images/users', $request->file->getClientOriginalName());
        $user->update(['image'=>$request->file->getClientOriginalName()]);

        return $this->respondAccepted();
  }
    /**
     * Auth user
     *
     * @param  EditUserRequest $request
     * @return Response
     */
  public function updateUser(EditUserRequest $request)
  {
    $user = User::getAuthenticated($request);
        $editedUser = [];
        $editedUser['first_name'] = $request->input('first_name');
        $editedUser['last_name'] = $request->input('last_name');
        $editedUser['email'] = $request->input('email');
        if ($request->has('password')){
            $editedUser['password'] = bcrypt($request->input('password'));
        }

        $user->update($editedUser);

        return $this->respondAccepted();
  }

  public function userRequiredOptions(GetRequiredOptionsRequest $request)
  {
    $user = User::getAuthenticated($request);

        return $this->respond($user);
  }
  public function userRequiredOptionsUpdate(GetRequiredOptionsRequest $request)
  {
    $user = User::getAuthenticated($request);

        $user->update([
            'required_fields'=>$request->input('required_fields')
        ]);

        return $this->respondAccepted();
  }

  public function getTeamMembersList() {
    $users = User::query();
    $users = $users->where("team_member" , 1)->get();

    foreach($users as $key => $value) {
            $users[$key]['imageURL'] = $value->image ? url("images/users/{$value->image}") : null;
    }

    return $this->respond($users);
  }
} 

 ?>

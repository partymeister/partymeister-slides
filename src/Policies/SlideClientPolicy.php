<?php

namespace Partymeister\Slides\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Motor\Admin\Models\User;
use Partymeister\Slides\Models\SlideClient;

class SlideClientPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     *
     * @param  \Motor\Backend\Models\User  $user
     * @param  string  $ability
     * @return void|bool
     */
    public function before(User $user, $ability)
    {
        if ($user->hasRole('SuperAdmin')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \Motor\Backend\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('slide_clients.read');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \Motor\Backend\Models\User  $user
     * @param  \Partymeister\Slides\Models\SlideClient  $slideClient
     * @return mixed
     */
    public function view(User $user, SlideClient $slideClient)
    {
        return $user->hasPermissionTo('slide_clients.read');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \Motor\Backend\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('slide_clients.write');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \Motor\Backend\Models\User  $user
     * @param  \Partymeister\Slides\Models\SlideClient  $slideClient
     * @return mixed
     */
    public function update(User $user, SlideClient $slideClient)
    {
        return $user->hasPermissionTo('slide_clients.write');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \Motor\Backend\Models\User  $user
     * @param  \Partymeister\Slides\Models\SlideClient  $slideClient
     * @return mixed
     */
    public function delete(User $user, SlideClient $slideClient)
    {
        return $user->hasPermissionTo('slide_clients.delete');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \Motor\Backend\Models\User  $user
     * @param  \Partymeister\Slides\Models\SlideClient  $slideClient
     * @return mixed
     */
    public function restore(User $user, SlideClient $slideClient)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \Motor\Backend\Models\User  $user
     * @param  \Partymeister\Slides\Models\SlideClient  $slideClient
     * @return mixed
     */
    public function forceDelete(User $user, SlideClient $slideClient)
    {
        //
    }
}

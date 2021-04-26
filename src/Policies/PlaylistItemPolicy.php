<?php

namespace Partymeister\Slides\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Motor\Backend\Models\User;
use Partymeister\Slides\Models\PlaylistItem;

class PlaylistItemPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     *
     * @param \Motor\Backend\Models\User $user
     * @param string $ability
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
        return $user->hasPermissionTo('playlist_items.read');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \Motor\Backend\Models\User  $user
     * @param  \Partymeister\Slides\Models\PlaylistItem  $playlistItem
     * @return mixed
     */
    public function view(User $user, PlaylistItem $playlistItem)
    {
        return $user->hasPermissionTo('playlist_items.read');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \Motor\Backend\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('playlist_items.write');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \Motor\Backend\Models\User  $user
     * @param  \Partymeister\Slides\Models\PlaylistItem  $playlistItem
     * @return mixed
     */
    public function update(User $user, PlaylistItem $playlistItem)
    {
        return $user->hasPermissionTo('playlist_items.write');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \Motor\Backend\Models\User  $user
     * @param  \Partymeister\Slides\Models\PlaylistItem  $playlistItem
     * @return mixed
     */
    public function delete(User $user, PlaylistItem $playlistItem)
    {
        return $user->hasPermissionTo('playlist_items.delete');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \Motor\Backend\Models\User  $user
     * @param  \Partymeister\Slides\Models\PlaylistItem  $playlistItem
     * @return mixed
     */
    public function restore(User $user, PlaylistItem $playlistItem)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \Motor\Backend\Models\User  $user
     * @param  \Partymeister\Slides\Models\PlaylistItem  $playlistItem
     * @return mixed
     */
    public function forceDelete(User $user, PlaylistItem $playlistItem)
    {
        //
    }
}

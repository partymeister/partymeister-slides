<?php

namespace Partymeister\Slides\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Motor\Admin\Models\User;
use Partymeister\Slides\Models\SlideTemplate;

class SlideTemplatePolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     *
     * @param  \Motor\Admin\Models\User  $user
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
     * @param  \Motor\Admin\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('slide_templates.read');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \Motor\Admin\Models\User  $user
     * @param  \Partymeister\Slides\Models\SlideTemplate  $slideTemplate
     * @return mixed
     */
    public function view(User $user, SlideTemplate $slideTemplate)
    {
        return $user->hasPermissionTo('slide_templates.read');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \Motor\Admin\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('slide_templates.write');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \Motor\Admin\Models\User  $user
     * @param  \Partymeister\Slides\Models\SlideTemplate  $slideTemplate
     * @return mixed
     */
    public function update(User $user, SlideTemplate $slideTemplate)
    {
        return $user->hasPermissionTo('slide_templates.write');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \Motor\Admin\Models\User  $user
     * @param  \Partymeister\Slides\Models\SlideTemplate  $slideTemplate
     * @return mixed
     */
    public function delete(User $user, SlideTemplate $slideTemplate)
    {
        return $user->hasPermissionTo('slide_templates.delete');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \Motor\Admin\Models\User  $user
     * @param  \Partymeister\Slides\Models\SlideTemplate  $slideTemplate
     * @return mixed
     */
    public function restore(User $user, SlideTemplate $slideTemplate)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \Motor\Admin\Models\User  $user
     * @param  \Partymeister\Slides\Models\SlideTemplate  $slideTemplate
     * @return mixed
     */
    public function forceDelete(User $user, SlideTemplate $slideTemplate)
    {
        //
    }
}

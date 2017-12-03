<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use App\Notifications\GenericNotificationWrapper;

use Auth;
use Mail;

use App\GASModel;

/*
    TODO In una futura versione, questo sarà da rimpiazzare col meccanismo delle
    notifiche nativo di Laravel
*/

class Notification extends Model
{
    use GASModel;

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('gas', function (Builder $builder) {
            $builder->whereHas('users', function($query) {
                $user = Auth::user();
                $query->where('gas_id', $user->gas->id);
            });
        });
    }

    public function users()
    {
        return $this->belongsToMany('App\User');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'creator_id');
    }

    public function hasUser($user)
    {
        foreach ($this->users as $u) {
            if ($u->id == $user->id) {
                return true;
            }
        }

        return false;
    }

    public function sendMail()
    {
        if ($this->mailed == false) {
            return;
        }

        foreach ($this->users as $user) {
            $user->notify(new GenericNotificationWrapper($this));

            /*
                Onde evitare di farsi bloccare dal server SMTP, qui attendiamo
                un pochino tra una mail e l'altra
            */
            usleep(200000);
        }
    }

    public function printableName()
    {
        $users = $this->users;
        $c = $users->count();

        if ($c == 1) {
            return $users->first()->printableName();
        } else {
            return sprintf('%d utenti', $c);
        }
    }

    public function printableHeader()
    {
        return $this->printableDate('start_date').' / '.$this->printableName();
    }
}

<?php

namespace app\Services;

use App\Exceptions\AuthException;
use App\Exceptions\PermissionException;
use App\Permission;
use Illuminate\Support\Facades\DB;

class PermissionsService
{

    public function showForSubject($subject_id, $rule_id)
    {
        $class = Permission::classByRule($rule_id);
        if ($class == null) {
            throw new PermissionException('Rule not found');
        }

        $subject = $class::findOrFail($subject_id);
        if ($subject->permissionsCanBeModified() == false) {
            throw new AuthException(401);
        }

        return $subject->whoCanComplex($rule_id);
    }

    public function add($user_id, $subject_id, $rule_id, $behaviour)
    {
        DB::transaction(function () use ($user_id, $subject_id, $rule_id, $behaviour) {
            $class = Permission::classByRule($rule_id);
            if ($class == null) {
                throw new PermissionException('Rule not found');
            }

            $subject = $class::findOrFail($subject_id);
            if ($subject->permissionsCanBeModified() == false) {
                throw new AuthException(401);
            }

            switch ($behaviour) {
                case 'all':
                    /*
                        Se tutti gli utenti sono autorizzati per
                        la regola, non può esistere il caso in
                        cui si intervenga su uno solo.
                        Cfr. postChange()
                    */
                    break;

                case 'selected':
                    $subject->userPermit($rule_id, $user_id);
                    break;

                case 'except':
                    $subject->userRevoke($rule_id, $user_id);
                    break;

                default:
                    throw new \Exception("Unknown behaviour");
            }
        });
    }
}

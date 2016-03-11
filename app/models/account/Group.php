<?php namespace Account;
use Xaircraft\Authentication\Auth;
use Xaircraft\Database\WhereQuery;
use Xaircraft\DB;
use Xaircraft\Exception\ExceptionHelper;
use Xaircraft\Nebula\BaseTree;
use Xaircraft\Nebula\Model;

/**
 * Date: 2016-03-09 14:58:42
 * @property mixed id
 * @property mixed name
 * @property mixed type
 * @property mixed parent_id
 * @property mixed company_id
 * @property mixed leader_uid
 * @property mixed status
 * @property mixed create_at
 * @property mixed create_by
 * @property mixed update_at
 * @property mixed update_by
 * @property mixed delete_at
 * @property mixed delete_by
 * @property mixed structure_id
 * @property mixed company_structure_id
 * @property mixed is_groups_child
 * @property mixed is_exclusive
 */
class Group extends Model
{
    use BaseTree;

    public function beforeSave()
    {

    }

    public function afterSave($isAppend = false)
    {
        // TODO: Implement afterSave() method.
    }

    public function beforeDelete()
    {

    }

    public function afterDelete($fields)
    {
        // TODO: Implement afterDelete() method.
    }

    public function afterForceDelete($fields)
    {
        // TODO: Implement afterForceDelete() method.
    }

    public function getParentIDField()
    {
        return "parent_id";
    }
}
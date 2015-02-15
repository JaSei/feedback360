<?php

include_once MODELS_DIR . "/util.php";
class Team
{
    public static function roles()
    {
        return [Session::MEMBER, Session::MANAGER];
    }

    public static function fetch_all()
    {
        return DB::queryFirstColumn("SELECT `id` FROM team");
    }

    public static function add_only_if_new($team_id, $team_name)
    {
        DB::insert('team', ['id'=>$team_id, 'name'=>$team_name]);
    }

    public static function members_of($org_id, $team_id)
    {
        $all_members = DB::query("select org_structure.username, user.name as member_name, org_structure.role from org_structure INNER JOIN user on user.`key`=org_structure.username where org_structure.org_id=%s and org_structure.team_id=%s ORDER BY org_structure.role", $org_id, $team_id);
        $team_name = DB::queryFirstField("select team.name from team where id=%s", $team_id);
        return ['org_id'=>$org_id, 'team_id'=>$team_id, 'team_name'=>$team_name, 'team_members'=>$all_members];
    }

    public static function delete($team_id, $org_id)
    {
        return ['status'=>'error', 'msg'=>"Sorry! We don't support delete yet..."];
    }

    public static function delete_member($username, $team_id, $org_id)
    {
        return ['status'=>'error', 'msg'=>"Sorry! We don't support delete yet..."];
    }

    public static function current_role_of($username, $team_id, $org_id)
    {
        return DB::queryFirstField("select role from org_structure where org_id=%s and team_id=%s and username=%s", $org_id, $team_id, $username);
    }

    public static function update_role($form, $username, $team_id, $org_id)
    {
        $role = $form['role'];
        $current_role = $form['current_role'];
        if($current_role!=$role)
            DB::update('org_structure', ['role'=>$role], "org_id=%s and team_id=%s and username=%s", $org_id, $team_id, $username);
    }

    public static function add_members($form, $team_id, $org_id)
    {
        $owner_details = [Session::get_user_property('email')=>Session::get_user_property('name')];
        $team_members = Util::tokenize_email_ids($form['team_members'], $owner_details);
        if (empty($team_members)) return "Team Members cannot be empty!";
        DB::startTransaction();
        try {
            self::save_org_structure($team_members, $team_id, $org_id);
        } catch (MeekroDBException $e) {
            DB::rollback();
            return "Could not save the details. Please try again. Error: ".$e->getMessage();
        }
        DB::commit();
        return 'success';
    }

    public static function save_org_structure($team_members, $team_id, $org_id, $manager = '')
    {
        $user_ids = User::create_only_if_new($team_members);

        $org_struct = [];

        if (!empty($manager))
            $org_struct[] = ['org_id' => $org_id, 'team_id' => $team_id, 'role' => Session::MANAGER, 'username' => $manager];

        foreach ($user_ids as $user_id) {
            $org_struct[] = ['org_id' => $org_id, 'team_id' => $team_id, 'role' => Session::MEMBER, 'username' => $user_id];
        }

        DB::insert('org_structure', $org_struct);
    }
}

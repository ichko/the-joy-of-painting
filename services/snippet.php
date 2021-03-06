<?php
namespace Services;

class SnippetService
{
    public function __construct(
        $navigation_service, $post_service, $auth_service, $db
    ) {
        $this->navigation_service = $navigation_service;
        $this->post_service = $post_service;
        $this->auth_service = $auth_service;
        $this->auth_service = $auth_service;
        $this->db = $db;
    }

    public function get_all()
    {
        return $this->db->query("
            SELECT
                s.user_id, u.name AS username,
                s.id, s.name, s.description, s.code,
                s.date_created
            FROM snippets s
            INNER JOIN users u ON s.user_id = u.id
        ")->execute()->fetch_all();
    }

    public function create()
    {
        return $this->db->query("
            INSERT INTO snippets (user_id, name)
            VALUES (:user_id, :name);
        ")->bind_all([
            'user_id' => $this->auth_service->get_logged_user_id(),
            'name' => 'Title',
        ])->execute()->get_last_id();
    }

    public function get($id)
    {
        return $this->db->query("
            SELECT
                s.user_id, u.name AS username,
                s.id, s.name, s.description, s.code,
                s.date_created
            FROM snippets AS s
            INNER JOIN users AS u ON s.user_id = u.id
            WHERE s.id = :id
        ")->bind_all(['id' => $id])->execute()->fetch();
    }

    public function save($id)
    {
        if (!$this->auth_service->is_logged()) {
            return [false, 'You need to log in'];
        }

        $snippet = $this->get($id);
        if ($snippet['user_id'] == $this->auth_service->get_logged_user_id()) {

            $fields = $this->post_service->get_json_post(['code', 'name', 'description']);
            $fields['id'] = $id;

            return [!!$this->db->query("
                UPDATE snippets
                SET code=:code, name=:name, description=:description
                WHERE id=:id;
            ")->bind_all($fields)->execute(), ''];
        }

        return [false, 'You can only save your own snippets'];
    }
}

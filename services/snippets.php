<?php
namespace Services;

class SnippetsService
{
    public function __construct(
        $navigation_service, $post_service, $auth_service, $db
    ) {
        $this->navigation_service = $navigation_service;
        $this->post_service = $post_service;
        $this->auth_service = $auth_service;
        $this->db = $db;
    }

    public function create()
    {
        return $this->db->query("
            INSERT INTO snippets (user_id)
            VALUES (:user_id);
        ")->bind_all([
            'user_id' => $this->auth_service->get_logged_user_id(),
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
}

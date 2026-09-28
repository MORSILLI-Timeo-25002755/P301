<?php

namespace Views\Member;


class Dashboard
{
    public function __construct(private string $username) {
    }

    public function show(): void
    {
        $rename = $this->username . '\' dashboard';
        begin_page($rename, '_assets/css/index.css');
        ?>
<p>Bonjour <?=$this->username?> </p>
    <?php }
}
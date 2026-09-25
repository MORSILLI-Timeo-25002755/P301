<?php
namespace Views; // PSR-12: head blocks must be separated by a single blank line
class Error { // PSR-12: opening brace next line

    public $message;

    public function __construct($message) {
        $this->message = $message;
    }

    public function show(): void { // PSR-12: opening brace next line
        ?>
        <h1 style="color:red;">Erreur: <?=$this->message?></h1>
<?php
    }
}
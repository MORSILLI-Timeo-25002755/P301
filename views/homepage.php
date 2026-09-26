<?php
namespace Views; // PSR-12: head blocks must be separated by a single blank line
class Homepage { // PSR-12: opening brace next line
    public function show(): void { // PSR-12: opening brace next line
        begin_page('Welcome','_assets/css/welcome.css');
        ?>
        <h1>Hello, world !</h1>
<?php
    }
}
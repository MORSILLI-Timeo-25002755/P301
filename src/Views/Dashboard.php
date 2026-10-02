<?php

namespace views;


readonly class Dashboard
{
    public function __construct(private string $username) {}

    public function show(): void
    {
        begin_page($this->username . '\'s dashboard', '/css/dashboard.css');
        ?>
        <div>
            <h2>Espace utilisateur</h2>
        </div>

        <h1>Tableau de bord</h1>

        <p>Gérez vos sondages, consultez vos statistiques et créez de nouvelles enquêtes en quelques clics.</p>

        <div>
            <article>
                <header>
                    <h4>Sondages créés</h4>
                </header>
                <p><?php ?></p>
            </article>

            <article>
                <header>
                    <h4>Réponses recueillies</h4>
                </header>
                <p><?php ?></p>
            </article>
            
            <article>
                <header>
                    <h4>Compte connecté</h4>
                </header>
                <p><?php ?></p>
            </article>
        </div>

        <section>
            <h3>Créer un nouveau sondage</h3>
            <p>Donnez un titre clair à votre sondage pour commencer à y associer vos questions.</p>

            <form>
                <input type="text" id="idtitle" name="title" placeholder="Ex : Sondage de satisfaction 2026..." autocomplete="off" required>
                <input type="submit" name="send" value="Créer le sondage">
            </form>
        </section>

        <section>
            <header>
                <h3>Mes sondages</h3>
                <div><?php ?></div>
            </header>

            <main>
                <?php ?>
            </main>
        </section>
    <?php }
}
<?php

namespace Views;


readonly class Dashboard
{
    public function __construct(private string $username) {}

    public function show(int $nb_form, int $nb_answer, string $user_mail, array $infos_form, int $current_page, int $total_pages): void
    {
        begin_page($this->username . '\'s dashboard', '/css/dashboard.css',
                link: "https://apocalypsehorsemen.alwaysdata.net/dashboard",
                description: "HorseForm, créez, partagez et personnalisez vos formulaires en quelques clics. Un outil simple, intuitif et rapide. Lancez-vous !");
        ?>
        <main class="body">
            <header class="dashboard-header">
                <div class="page-descriptor">
                    <h2>Espace utilisateur</h2>
                </div>

                <h1>Tableau de bord</h1>

                <p>Gérez vos sondages, consultez vos statistiques et créez de nouvelles enquêtes en quelques clics.</p>
            </header>
            
            <main>
                <section class="statistical-information">
                    <article>
                        <header>
                            <h4>Sondages créés</h4>
                        </header>
                        <p class="stat-value"><?php echo $nb_form; ?></p>
                    </article>

                    <article>
                        <header>
                            <h4>Réponses recueillies</h4>
                        </header>
                        <p class="stat-value"><?php echo $nb_answer; ?></p>
                    </article>
                    
                    <article>
                        <header>
                            <h4>Compte connecté</h4>
                        </header>
                        <p class="account"><?php echo $user_mail; ?></p>
                    </article>
                </section>

                <section class="new-survey">
                    <h3>Créer un nouveau sondage</h3>
                    <p>Donnez un titre clair à votre sondage pour commencer à y associer vos questions.</p>

                    <form>
                        <input type="text" id="idtitle" name="title" placeholder="Ex : Sondage de satisfaction 2026..." autocomplete="off" required>
                        <button type="submit">Créer le sondage</button>
                    </form>
                </section>

                <section class="my-survey" id="mes-sondages">
                    <header>
                        <h3>Mes sondages</h3>
                        <p class="surgey-counter"><?php echo $nb_form; echo ($nb_form > 1 ? " sondages" : " sondage"); ?></p>
                    </header>

                    <?php if($nb_form == 0): ?>
                        <section class="none-survey">
                            <span class="notepad" aria-hidden="true">📋</span>
                            <h5>Vous n'avez pas encore créé de sondage</h5>
                            <p>Utilisez le formulaire ci-dessus pour lancer votre tout premier sondage.</p>
                        </section>

                    <?php else: ?>
                        <section class="survey-list">

                            <?php foreach($infos_form as $tuple): ?>
                                <article class="survey-element">
                                    <header>
                                        <h5><?php echo htmlspecialchars($tuple['name']); ?></h5>
                                    </header>
                                    <footer class="survey-main-element">
                                        <ul class="stat-survey">
                                            <li><?php echo $tuple['nb_questions']; echo ($tuple['nb_questions'] > 1 ? " questions" : " question"); ?></li>
                                            <li><?php echo $tuple['nb_answers']; echo ($tuple['nb_answers'] > 1 ? " réponses" : " réponse"); ?></li>
                                        </ul>
                                        <button type="button">Voir / Gérer</button>
                                    </footer>
                                </article>

                            <?php endforeach ?>
                        </section>

                        <footer class="survey-pagination">
                            <?php if($current_page > 1): ?>
                                <a href="?page=<?php echo $current_page - 1; ?>#mes-sondages" class="button-pagination" aria-label="Page précédente">&lt;</a>
                            <?php endif; ?>

                            <!-- Affichage des pages avec un élément sémantique neutre -->
                            <span class="pagination-info">Page <?php echo $current_page; ?> sur <?php echo $total_pages; ?></span>

                            <?php if($current_page < $total_pages): ?>
                                <a href="?page=<?php echo $current_page + 1; ?>#mes-sondages" class="button-pagination" aria-label="Page suivante">&gt;</a>
                            <?php endif; ?>
                        </footer>

                    <?php endif; ?>

                </section>
            </main>
        </main>
    <?php  end_page(); }
}
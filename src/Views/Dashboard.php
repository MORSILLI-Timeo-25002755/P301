<?php

namespace Views;


readonly class Dashboard
{
    public function __construct(private string $username) {}

    public function show(int $nb_form, int $nb_answer, string $user_mail, array $infos_form, int $current_page, int $total_pages): void
    {
        begin_page($this->username . '\'s dashboard', '/css/dashboard.css');
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

                <section class="my-survey">
                    <header>
                        <h3>Mes sondages</h3>
                        <div class="surgey-counter"><?php echo $nb_form; echo ($nb_form > 1 ? " sondages" : " sondage"); ?></div>
                    </header>

                    <?php if($nb_form == 0): ?>
                    <main class="none-survey">
                        <div class="notepad">📋</div>
                        <h5>Vous n'avez pas encore créé de sondage</h5>
                        <p>Utilisez le formulaire ci-dessus pour lancer votre tout premier sondage.</p>
                    </main>

                    <?php else: ?>
                    <main class="survey-list">

                    <?php foreach($infos_form as $tuple): ?>
                        <article class="survey-element">
                            <h5><?php echo htmlspecialchars($tuple['name']); ?></h5>
                            <div class="survey-main-element">
                                <div class="stat-survey">
                                    <p><?php echo $tuple['nb_questions']; echo ($tuple['nb_questions'] > 1 ? " questions" : " question"); ?></p>
                                    <p><?php echo $tuple['nb_answers']; echo ($tuple['nb_answers'] > 1 ? " réponses" : " réponse"); ?></p>
                                </div>
                                <button type="button">Voir / Gérer</button>
                            </div>
                        </article>

                    <?php endforeach ?>
                    </main>

                    <footer class="survey-pagination">
                        <?php if($current_page > 1): ?>
                        <a href="?page=<?php echo $current_page == 1 ? $current_page : $current_page - 1; ?>"><button class="button-pagination"><</button></a>
                        <?php endif; ?>
                        <?php if($current_page < $total_pages): ?>
                        <a href="?page=<?php echo $current_page + 1; ?>"><button class="button-pagination">></button></a>
                        <?php endif; ?>
                    </footer>

                    <?php endif; ?>

                </section>
            </main>
        </main>
    <?php }
}
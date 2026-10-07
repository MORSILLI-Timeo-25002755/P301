<?php

namespace Views;

readonly class MentionsLegales
{

    public function __construct()
    {
    }

    public function show(): void
    {
        begin_page('MentionsLegales', '/css/MentionsLegales.css',
            link: "https://apocalypsehorsemen.alwaysdata.net/MentionLegales");
        ?>

        <main class="legal-page">

            <header class="legal-header">
                <p class="page-descriptor">Droits utilisateurs</p>

                <h1>Mentions Légales</h1>

                <p class="description">Cette page présente les informations légales relatives au projet d'étude HorseForm</p>
            </header>

            <article class="legal-card">

                <section class="legal-section">
                    <h2>Application</h2>
                    <p><strong>Éditeur :</strong> Lohann BALBAS</p>
                    <p>
                        <strong>Raison sociale et dénomination :</strong> le directeur de la publication est
                        une personne physique désireuse de garder son anonymat. Les coordonnées exactes de
                        l'éditeur ont donc été transmises de manière complète à l'hébergeur. C'est l'hébergeur
                        qui peut être tenu de communiquer les informations sur l'éditeur, mais uniquement dans
                        le cadre d'une procédure judiciaire.
                    </p>
                    <p>
                        <strong>Conception et réalisation du site web :</strong> Lohann BALBAS,
                        Gaël BARTHELEMY, Timéo MORSILLI, Mattéo YANNI
                    </p>
                    <p>
                        <strong>Contact :</strong>
                        <a href="mailto:apocalypsehorsemen@alwaysdata.net">apocalypsehorsemen@alwaysdata.net</a>
                    </p>
                </section>

                <section class="legal-section">
                    <h2>Hébergement</h2>
                    <p>
                        Le site est hébergé par la société ALWAYSDATA, SARL au capital de 200 000 €,
                        immatriculée au RCS de Paris sous le numéro 492 893 490, dont le siège social est situé
                        au 91 rue du Faubourg Saint-Honoré, 75008 Paris, France.
                    </p>
                    <p>Téléphone : +33 1 84 16 23 40</p>
                    <p>
                        Site web :
                        <a href="https://www.alwaysdata.com" target="_blank" rel="noopener noreferrer">www.alwaysdata.com</a>
                    </p>
                </section>

                <section class="legal-section">
                    <h2>Politique de confidentialité</h2>

                    <h3>Responsable du traitement</h3>
                    <p>
                        Lohann BALBAS -
                        <a href="mailto:apocalypsehorsemen@alwaysdata.net">apocalypsehorsemen@alwaysdata.net</a>
                    </p>

                    <h3>Données collectées</h3>
                    <p><strong>Adresse e-mail</strong></p>
                    <p>
                        Finalité : créer et gérer le compte, vous connecter, envoyer les messages liés au
                        service (confirmation, réinitialisation du mot de passe, notifications).
                    </p>
                    <p>
                        Base légale : exécution du service demandé. La collecte de cette donnée est autorisée
                        à ce titre.
                    </p>

                    <h3>Destinataires des données</h3>
                    <p>
                        Vos données personnelles sont accessibles uniquement à l'éditeur du site. Elles sont
                        hébergées par la société ALWAYSDATA (Paris, France), qui agit en tant que sous-traitant
                        et ne les utilise que pour fournir le service d'hébergement.
                    </p>
                    <p>
                        Vos données ne sont ni vendues, ni cédées à des tiers à des fins commerciales. Elles
                        peuvent toutefois être communiquées aux autorités compétentes sur réquisition légale.
                    </p>

                    <h3>Durée de conservation</h3>
                    <p>
                        Vos données sont conservées tant que votre compte est actif. Vous pouvez également
                        supprimer votre compte à tout moment, ce qui entraîne l'effacement de vos données,
                        sous réserve des obligations légales de conservation éventuelles.
                    </p>

                    <h3>Vos droits</h3>
                    <p>
                        Conformément au RGPD et à la loi Informatique et Libertés, vous disposez des droits
                        suivants sur vos données :
                    </p>
                    <ul>
                        <li><strong>Accès :</strong> obtenir une copie des données vous concernant ;</li>
                        <li><strong>Rectification :</strong> faire corriger des données inexactes ou incomplètes ;</li>
                        <li><strong>Effacement :</strong> demander la suppression de vos données ;</li>
                        <li><strong>Opposition :</strong> vous opposer à un traitement pour des raisons tenant à votre situation particulière ;</li>
                        <li><strong>Limitation :</strong> demander la suspension temporaire d'un traitement ;</li>
                        <li>
                            <strong>Portabilité :</strong> recevoir vos données dans un format structuré et
                            lisible par machine, lorsque le traitement repose sur votre consentement ou sur
                            l'exécution du service.
                        </li>
                    </ul>
                    <p>
                        Pour exercer ces droits, écrivez à :
                        <a href="mailto:apocalypsehorsemen@alwaysdata.net">apocalypsehorsemen@alwaysdata.net</a>.
                        Nous pourrons vous demander un justificatif d'identité en cas de doute raisonnable.
                    </p>

                    <h3>Droit de réclamation auprès de la CNIL</h3>
                    <p>
                        Si vous estimez, après nous avoir contactés, que vos droits ne sont pas respectés, vous
                        pouvez introduire une réclamation auprès de la CNIL (Commission nationale de
                        l'informatique et des libertés) :
                        <a href="https://www.cnil.fr" target="_blank" rel="noopener noreferrer">www.cnil.fr</a>,
                        rubrique « Plaintes », ou par courrier : 3 Place de Fontenoy, TSA 80715,
                        75334 Paris Cedex 07.
                    </p>

                    <h3>Transferts hors Union européenne</h3>
                    <p>
                        Vos données sont hébergées en France par la société ALWAYSDATA. Elles ne font l'objet
                        d'aucun transfert en dehors de l'Union européenne.
                    </p>
                </section>
            </article>
        </main>



<?php
        end_page();
    } // show
} // class
?>
<?php common('header-nav'); ?>

<main id="main">
    <?php
    hero("page", [
        "title" => "Styles"
    ]);
    ?>
    <div class="strate starter-styleguide">
        <section class="starter-section">
            <header class="starter-section-header">
                <h2 class="title title-2">Typographie</h2>
                <p>Styles de base pour les fonts, titres, textes et contenu enrichi.</p>
            </header>

            <div class="starter-grid">
                <article class="starter-panel">
                    <h3 class="title title-3">Fonts</h3>
                    <div class="starter-spec-list">
                        <p><strong>Font 1</strong> <span>Roboto / Arial / sans-serif</span></p>
                        <p><strong>Font 2</strong> <span>Roboto / Arial / sans-serif</span></p>
                    </div>
                    <p class="starter-font-preview">Aa Bb Cc Dd Ee Ff 0123456789</p>
                </article>

                <article class="starter-panel">
                    <h3 class="title title-3">Titles</h3>
                    <table class="starter-table">
                        <thead>
                            <tr>
                                <th scope="col">Class</th>
                                <th scope="col">Exemple</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>.title .title-1</code></td>
                                <td><p class="title title-1">Title 1</p></td>
                            </tr>
                            <tr>
                                <td><code>.title .title-2</code></td>
                                <td><p class="title title-2">Title 2</p></td>
                            </tr>
                            <tr>
                                <td><code>.title .title-3</code></td>
                                <td><p class="title title-3">Title 3</p></td>
                            </tr>
                            <tr>
                                <td><code>.title .title-4</code></td>
                                <td><p class="title title-4">Title 4</p></td>
                            </tr>
                        </tbody>
                    </table>
                </article>

                <article class="starter-panel">
                    <h3 class="title title-3">Texts</h3>
                    <table class="starter-table">
                        <thead>
                            <tr>
                                <th scope="col">Class / balise</th>
                                <th scope="col">Exemple</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>.text</code></td>
                                <td><div class="text"><p>Texte courant du starter. Il sert a verifier la lisibilite.</p></div></td>
                            </tr>
                            <tr>
                                <td><code>.rte p</code></td>
                                <td><div class="rte"><p>Paragraphe dans un contenu enrichi.</p></div></td>
                            </tr>
                            <tr>
                                <td><code>small</code></td>
                                <td><small>Texte secondaire en small.</small></td>
                            </tr>
                            <tr>
                                <td><code>.rte a</code></td>
                                <td><div class="rte"><p><a href="#">Lien dans un contenu enrichi</a></p></div></td>
                            </tr>
                        </tbody>
                    </table>
                </article>

                <article class="starter-panel">
                    <h3 class="title title-3">Rte</h3>
                    <div class="rte">
                        <h2>Titre RTE niveau 2</h2>
                        <p>Paragraphe avec un <a href="#">lien</a> et une <strong>mise en avant</strong>.</p>
                        <ul>
                            <li>Element de liste</li>
                            <li>Element de liste</li>
                        </ul>
                    </div>
                </article>
            </div>
        </section>

        <section class="starter-section">
            <header class="starter-section-header">
                <h2 class="title title-2">Colors</h2>
                <p>Variables de couleurs principales disponibles dans le starter.</p>
            </header>

            <div class="starter-swatches">
                <div class="starter-swatch" style="--swatch: var(--color-text);">
                    <span></span>
                    <strong>Text</strong>
                    <code>--color-text</code>
                </div>
                <div class="starter-swatch" style="--swatch: var(--color-1);">
                    <span></span>
                    <strong>Color 1</strong>
                    <code>--color-1</code>
                </div>
                <div class="starter-swatch" style="--swatch: var(--color-2);">
                    <span></span>
                    <strong>Color 2</strong>
                    <code>--color-2</code>
                </div>
                <div class="starter-swatch" style="--swatch: var(--color-3);">
                    <span></span>
                    <strong>Color 3</strong>
                    <code>--color-3</code>
                </div>
                <div class="starter-swatch" style="--swatch: var(--color-4);">
                    <span></span>
                    <strong>Color 4</strong>
                    <code>--color-4</code>
                </div>
                <div class="starter-swatch" style="--swatch: var(--color-5);">
                    <span></span>
                    <strong>Color 5</strong>
                    <code>--color-5</code>
                </div>
                <div class="starter-swatch" style="--swatch: var(--color-6);">
                    <span></span>
                    <strong>Color 6</strong>
                    <code>--color-6</code>
                </div>
                <div class="starter-swatch" style="--swatch: var(--color-gray-light);">
                    <span></span>
                    <strong>Gray light</strong>
                    <code>--color-gray-light</code>
                </div>
                <div class="starter-swatch" style="--swatch: var(--color-gray);">
                    <span></span>
                    <strong>Gray</strong>
                    <code>--color-gray</code>
                </div>
                <div class="starter-swatch" style="--swatch: var(--color-gray-dark);">
                    <span></span>
                    <strong>Gray dark</strong>
                    <code>--color-gray-dark</code>
                </div>
            </div>
        </section>

        <section class="starter-section">
            <header class="starter-section-header">
                <h2 class="title title-2">Backgrounds</h2>
                <p>Classes utilitaires de fond et comportement texte associe.</p>
            </header>

            <div class="starter-backgrounds">
                <div class="starter-background bg-color-1">
                    <strong>.bg-color-1</strong>
                    <span>Fond brand principal</span>
                </div>
                <div class="starter-background bg-color-2">
                    <strong>.bg-color-2</strong>
                    <span>Fond brand secondaire</span>
                </div>
                <div class="starter-background bg-color-3">
                    <strong>.bg-color-3</strong>
                    <span>Fond clair</span>
                </div>
            </div>
        </section>

        <section class="starter-section">
            <header class="starter-section-header">
                <h2 class="title title-2">Layout</h2>
                <p>Repere visuel des containers et espacements disponibles.</p>
            </header>

            <div class="starter-layout-demo">
                <div class="starter-layout-row">
                    <span>full</span>
                    <div class="starter-layout-bar starter-layout-full"></div>
                </div>
                <div class="starter-layout-row">
                    <span>fluid</span>
                    <div class="starter-layout-bar starter-layout-fluid"></div>
                </div>
                <div class="starter-layout-row">
                    <span>ctr</span>
                    <div class="starter-layout-bar starter-layout-ctr"></div>
                </div>
                <div class="starter-layout-row">
                    <span>ctr-sm</span>
                    <div class="starter-layout-bar starter-layout-ctr-sm"></div>
                </div>
            </div>

            <div class="starter-grid starter-grid-compact">
                <article class="starter-panel">
                    <h3 class="title title-3">Margins</h3>
                    <div class="starter-spec-list">
                        <p><strong>XS</strong> <span>var(--margin-xs)</span></p>
                        <p><strong>SM</strong> <span>var(--margin-sm)</span></p>
                        <p><strong>MD</strong> <span>var(--margin-md)</span></p>
                        <p><strong>LG</strong> <span>var(--margin-lg)</span></p>
                    </div>
                </article>

                <article class="starter-panel">
                    <h3 class="title title-3">Grid</h3>
                    <div class="starter-spec-list">
                        <p><strong>Columns</strong> <span>12</span></p>
                        <p><strong>Gap</strong> <span>var(--gap)</span></p>
                        <p><strong>Offset</strong> <span>var(--ctr-offset)</span></p>
                        <p><strong>Max width</strong> <span>var(--ctr-width)</span></p>
                    </div>
                </article>
            </div>
        </section>
    </div>

</main>

<?php common('footer'); ?>

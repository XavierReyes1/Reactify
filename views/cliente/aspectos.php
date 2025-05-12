<?php include_once '../templates/header_cliente.php'; ?>

   <div class="survey-container">
        <header>
            <h1>¿Hay algo que quieras destacar?</h1>
            <p>Selecciona los aspectos que consideres relevantes (opcional).</p>
        </header>

        <main>
            <div id="aspectsContainer" class="aspect-selection">
                <h2>Aspectos Positivos:</h2>
                <div class="aspect-buttons">
                    <button class="aspect-button" data-aspect="Rapidez">Rapidez</button>
                    <button class="aspect-button" data-aspect="Amabilidad">Amabilidad en el Trato</button>
                    <button class="aspect-button" data-aspect="Claridad">Claridad de la Información</button>
                    <button class="aspect-button" data-aspect="Resolucion">Resolución Efectiva</button>
                    <button class="aspect-button" data-aspect="Instalaciones">Instalaciones Limpias</button>
                    </div>
                </div>

            <button id="submitAspectsButton" class="button">Enviar Opinión</button>
            </main>


 <?php include_once '../templates/footer_cliente.php'; ?>
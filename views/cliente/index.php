<?php include_once '../templates/header_cliente.php'; ?>


<div class="survey-container">
        <header>
            <h1>¡Bienvenido!</h1>
            <p>Queremos saber cómo fue tu experiencia hoy.</p>
            <p>Por favor, califica tu nivel de satisfacción:</p>
        </header>

        <main>
            <div class="satisfaction-scale">
                <div class="emoji-option" data-value="1" role="button" aria-label="Muy insatisfecho">
                    <span class="emoji">😞</span>
                    <span class="emoji-label">Muy Mal</span>
                </div>
                <div class="emoji-option" data-value="2" role="button" aria-label="Insatisfecho">
                    <span class="emoji">🙁</span>
                    <span class="emoji-label">Mal</span>
                </div>
                <div class="emoji-option" data-value="3" role="button" aria-label="Neutral">
                    <span class="emoji">😐</span>
                    <span class="emoji-label">Regular</span>
                </div>
                <div class="emoji-option" data-value="4" role="button" aria-label="Satisfecho">
                    <span class="emoji">🙂</span>
                    <span class="emoji-label">Bien</span>
                </div>
                <div class="emoji-option" data-value="5" role="button" aria-label="Muy satisfecho">
                    <span class="emoji">😄</span>
                    <span class="emoji-label">Muy Bien</span>
                </div>
            </div>

            <input type="hidden" id="satisfactionLevel" name="satisfactionLevel">

            <div class="area-selection">
                <label for="area">¿En qué área fuiste atendido?</label>
                <select id="area" name="area">
                    <option value="">Selecciona un área...</option>
                    <option value="1">Ventas</option>
                    <option value="2">Atención al Cliente</option>
                    <option value="3">Soporte Técnico</option>
                    </select>
            </div>

            <button id="nextButton" class="button" disabled>Siguiente</button>
        </main>

 <?php include_once '../templates/footer_cliente.php'; ?>
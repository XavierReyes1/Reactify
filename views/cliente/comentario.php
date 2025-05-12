<?php include_once '../templates/header_cliente.php'; ?>


   <div class="survey-container" id="commentContainer">
        <header>
            <h1>Lamentamos tu experiencia</h1>
            <p>Por favor, ayúdanos a mejorar. ¿Podrías contarnos qué sucedió o qué podríamos hacer diferente?</p>
        </header>

        <main>
            <form id="commentForm">
                <div class="comment-field">
                    <label for="commentText">Tu comentario:</label>
                    <textarea id="commentText" name="commentText" placeholder="Escribe aquí tus comentarios..."></textarea>
                </div>
                <button type="button" id="submitCommentButton" class="button">Enviar Comentario</button>
            </form>
        </main>


 <?php include_once '../templates/footer_cliente.php'; ?>
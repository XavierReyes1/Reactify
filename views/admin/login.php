   <?php $titulo = 'Inicio de Sesion';  include_once '../templates/header_admin.php'; ?>
   
   
   <div class="login-container">
        <form id="loginForm" class="login-form" method="GET">
            <h2>Panel de Administración</h2>
            <p>Reactify Encuestas</p>
            <div class="form-group">
                <label for="username">Usuario:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="button">Ingresar</button>
            <p id="loginMessage" class="login-message"></p>
        </form>
    </div>
     <?php include_once '../templates/footer_cliente.php'; ?>
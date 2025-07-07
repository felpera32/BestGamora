<header>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">GM</a>

            <div class="nav-menu">
                <ul class="nav-links">
                    <li><a href="vendedor.php" class="nav-item">ANUNCIAR</a></li>
                    <li><a href="meus_produtos.php" class="nav-item">Meus Produtos</a></li>
                    
                </ul>
            </div>



            <div class="user-actions">
                <button class="cart-button" id="cart">
                    <span class="cart-icon">🛒</span>
                </button>

                <?php
                if (isset($_SESSION['usuario'])) {
                    // pega a foto do usuário ou define o padrão
                    $fotoPerfil = !empty($_SESSION['usuario']['foto']) ? $_SESSION['usuario']['foto'] : 'uploads/default.png';
                    echo '<div class="profile-wrapper">';
                    echo '<img src="' . htmlspecialchars($fotoPerfil) . '" alt="Perfil" class="profile-photo" id="foto-perfil">';
                    echo '<div class="profile-dropdown">';
                    echo '<a href="perfil.php">Perfil da loja</a>';
                    echo '<a href="Meus Produtos.php">Publicados</a>';
                    echo '<a href="navbar/logout.php">Sair</a>';
                    echo '</div>';
                    echo '</div>';
                } else {
                    echo '<button class="login-button" onclick="entrarpag()" id="enter">Entrar</button>';
                }
                ?>
            </div>


        </div>
    </nav>
</header>


<style>
    .navbar {
        background-color: #0a1428;
        color: white;
        padding: 0;
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    }

    .nav-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0.8rem 1rem;
    }

    .logo {
        font-size: 1.5rem;
        font-weight: bold;
        color: white;
        text-decoration: none;
        background: linear-gradient(135deg, #5cb85c, #3e8e41);
        padding: 0.4rem 0.8rem;
        border-radius: 4px;
        transition: all 0.3s ease;
    }

    .logo:hover {
        transform: scale(1.05);
        box-shadow: 0 0 10px rgba(92, 184, 92, 0.5);
    }

    .nav-menu {
        display: flex;
    }

    .nav-links {
        display: flex;
        list-style: none;
        margin: 0;
        padding: 0;
        gap: 1.5rem;
    }

    .nav-item {
        color: white;
        text-decoration: none;
        font-weight: 600;
        font-size: 1rem;
        position: relative;
        padding: 0.5rem 0;
        transition: color 0.3s ease;
    }

    .nav-item:after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: 0;
        left: 0;
        background-color: #5cb85c;
        transition: width 0.3s ease;
    }

    .nav-item:hover {
        color: #5cb85c;
    }

    .nav-item:hover:after {
        width: 100%;
    }

    .search-container {
        display: flex;
        flex: 1;
        max-width: 400px;
        margin: 0 1.5rem;
    }

    .search-container form {
        display: flex;
        width: 100%;
    }

    .search-input {
        flex: 1;
        padding: 0.6rem 1rem;
        border: none;
        border-radius: 4px 0 0 4px;
        font-size: 0.9rem;
        outline: none;
    }

    .search-button {
        background-color: #5cb85c;
        color: white;
        border: none;
        border-radius: 0 4px 4px 0;
        padding: 0 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .search-button:hover {
        background-color: #4cae4c;
    }

    .user-actions {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .cart-button {
        background: none;
        border: none;
        position: relative;
        cursor: pointer;
        padding: 0.5rem;
    }

    .cart-icon {
        font-size: 1.3rem;
    }



    .profile-wrapper {
        position: relative;
        cursor: pointer;
    }

    .profile-photo {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #5cb85c;
    }

    .profile-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        background-color: #fff;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        border-radius: 4px;
        width: 180px;
        display: none;
        z-index: 1001;
    }

    .profile-wrapper:hover .profile-dropdown {
        display: block;
    }

    .profile-dropdown a {
        display: block;
        padding: 0.8rem 1rem;
        color: #333;
        text-decoration: none;
        transition: background-color 0.3s;
    }

    .profile-dropdown a:hover {
        background-color: #f0f0f0;
    }

    .login-button {
        background-color: #5cb85c;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 0.5rem 1.2rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .login-button:hover {
        background-color: #4cae4c;
        transform: translateY(-2px);
    }

    .mobile-menu-button {
        display: none;
        background: none;
        border: none;
        cursor: pointer;
        flex-direction: column;
        justify-content: space-between;
        height: 24px;
        width: 30px;
        padding: 0;
    }

    .bar {
        height: 3px;
        width: 100%;
        background-color: white;
        border-radius: 3px;
        transition: all 0.3s ease;
    }

    @media screen and (max-width: 900px) {
        .nav-container {
            padding: 0.8rem;
        }

        .search-container {
            max-width: 250px;
            margin: 0 1rem;
        }
    }

    @media screen and (max-width: 768px) {
        .mobile-menu-button {
            display: flex;
        }

        .nav-menu {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background-color: #0a1428;
            flex-direction: column;
            padding: 1rem 0;
            display: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .nav-menu.active {
            display: flex;
        }

        .nav-links {
            flex-direction: column;
            gap: 0;
        }

        .nav-links li {
            width: 100%;
        }

        .nav-item {
            display: block;
            padding: 1rem 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .search-container {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            margin: 0;
            max-width: 100%;
            background-color: #0a1428;
            padding: 1rem;
            display: none;
            box-sizing: border-box;
        }

        .search-container.active {
            display: flex;
        }
    }
</style>
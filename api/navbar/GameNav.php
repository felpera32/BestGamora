<?php
class Database {
    private $servidor = "mysql-gamora.alwaysdata.net";
    private $usuario = "gamora";
    private $senha = "trabalho6969*";
    private $banco = "gamora_loja";
    private $conn;
    
    public function __construct() {
        $this->conn = new mysqli($this->servidor, $this->usuario, $this->senha, $this->banco);
        
        if ($this->conn->connect_error) {
            error_log("Erro de conexão: " . $this->conn->connect_error);
        }
    }
    
    public function getMoedasUsuario($userId) {
        if (!$this->conn || $this->conn->connect_error) {
            return 0;
        }
        
        $stmt = $this->conn->prepare("SELECT moedas FROM clientes WHERE idCliente = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return (int)$row['moedas'];
        }
        
        return 0;
    }
    
    // Método adicional para buscar dados completos do usuário incluindo foto
    public function getDadosUsuario($userId) {
        if (!$this->conn || $this->conn->connect_error) {
            return null;
        }
        
        // Usando os nomes corretos das colunas conforme o banco de dados
        $stmt = $this->conn->prepare("SELECT nome, moedas FROM clientes WHERE idCliente = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

function getMoedasUsuario() {
    if (!isset($_SESSION['usuario']['id'])) {
        return 0;
    }
    
    $db = new Database();
    return $db->getMoedasUsuario($_SESSION['usuario']['id']);
}

function getDadosUsuarioCompletos() {
    if (!isset($_SESSION['usuario']['id'])) {
        return null;
    }
    
    $db = new Database();
    return $db->getDadosUsuario($_SESSION['usuario']['id']);
}

// Buscar dados completos do usuário
$dadosUsuario = getDadosUsuarioCompletos();
$moedas = $dadosUsuario ? $dadosUsuario['moedas'] : 0;
?>

<header>
    <nav class="navbar" role="navigation" aria-label="Menu principal">
        <div class="nav-container">
            <!-- Logo -->
            <a href="../index.php" class="logo" aria-label="Página inicial GM Store">
                <span class="logo-text">GM</span>
            </a>

            <!-- Menu de navegação -->
            <div class="nav-menu" id="nav-menu">
                <ul class="nav-links" role="menubar">
                    <li role="none">
                        <a href="../index.php" class="nav-item" role="menuitem">
                            LOJA
                        </a>
                    </li>
                    <li role="none">
                        <a href="../biblioteca.php" class="nav-item" role="menuitem">
                            BIBLIOTECA
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Ações do usuário -->
            <div class="user-actions">
                <!-- Contador de moedas -->
                <div class="coins-container" title="Suas moedas">
                    <span class="coin-icon" aria-hidden="true">🪙</span>
                    <span class="coin-amount" aria-label="<?php echo $moedas; ?> moedas">
                        <?php echo number_format($moedas, 0, ',', '.'); ?>
                    </span>
                </div>

                <button class="cart-button" id="cart" aria-label="Abrir carrinho de compras" title="Carrinho">
                    <span class="cart-icon" aria-hidden="true">🛒</span>
                    <span class="cart-badge" id="cart-badge" style="display: none;">0</span>
                </button>

                <?php if (isset($_SESSION['usuario'])): ?>
                    <div class="profile-wrapper" tabindex="0" role="button" aria-haspopup="true" aria-expanded="false">
                        <?php

                        $userId = $_SESSION['usuario']['id'];
                        $extensoes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        $fotoPerfil = '../uploads/default.png'; 
                        
                        foreach ($extensoes as $ext) {
                            $arquivoPossivel = "uploads/perfil_{$userId}.{$ext}";
                            if (file_exists($arquivoPossivel)) {
                                $fotoPerfil = $arquivoPossivel;
                                break;
                            }
                        }
                        

                        $nomeUsuario = '';
                        if ($dadosUsuario && !empty($dadosUsuario['nome'])) {
                            $nomeUsuario = htmlspecialchars($dadosUsuario['nome'], ENT_QUOTES, 'UTF-8');
                        } else {
                            $nomeUsuario = htmlspecialchars($_SESSION['usuario']['nome'] ?? 'Usuário', ENT_QUOTES, 'UTF-8');
                        }
                        ?>
                        <img src="<?php echo htmlspecialchars($fotoPerfil, ENT_QUOTES, 'UTF-8'); ?>" 
                             alt="Foto de perfil de <?php echo $nomeUsuario; ?>"
                             class="profile-photo" 
                             id="foto-perfil"
                             onerror="this.src='uploads/default.png'">
                        
                        <div class="profile-dropdown" role="menu" aria-label="Menu do usuário">
                            <div class="profile-info">
                                <span class="profile-name"><?php echo $nomeUsuario; ?></span>
                            </div>
                            <hr class="dropdown-separator">
                            <a href="../perfil.php" role="menuitem">
                                <i class="icon-user" aria-hidden="true"></i>
                                Meu Perfil
                            </a>
                            <a href="../pedidos.php" role="menuitem">
                                <i class="icon-orders" aria-hidden="true"></i>
                                Meus Pedidos
                            </a>
                            <a href="configuracoes.php" role="menuitem">
                                <i class="icon-settings" aria-hidden="true"></i>
                                Configurações
                            </a>
                            <hr class="dropdown-separator">
                            <a href="logout.php" role="menuitem" class="logout-link">
                                <i class="icon-logout" aria-hidden="true"></i>
                                Sair
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <button class="login-button" onclick="entrarpag()" id="enter" aria-label="Fazer login">
                        <i class="icon-login" aria-hidden="true"></i>
                        Entrar
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>

<style>
:root {
    --primary-color: #5cb85c;
    --primary-hover: #4cae4c;
    --primary-dark: #3e8e41;
    --background-dark: #0a1428;
    --background-light: rgba(255, 255, 255, 0.1);
    --text-white: #ffffff;
    --text-muted: #cccccc;
    --shadow-light: 0 2px 8px rgba(0, 0, 0, 0.3);
    --shadow-heavy: 0 4px 16px rgba(0, 0, 0, 0.4);
    --border-radius: 8px;
    --transition-fast: all 0.2s ease;
    --transition-normal: all 0.3s ease;
}

* {
    box-sizing: border-box;
}

.navbar {
    margin-left: -40px;
    width: 100%;
    background: linear-gradient(135deg, var(--background-dark) 0%, #1a2332 100%);
    color: var(--text-white);
    padding: 0;
    position: absolute;
    top: 0;
    z-index: 1000;
    box-shadow: var(--shadow-light);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.nav-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    max-width: 1600px; /* Aumentado para comportar mais espaço */
    margin: 0 auto;
    padding: 1rem 3rem; /* Aumentado para 3rem */
    position: relative;
    gap: 6rem; /* MUITO MAIS ESPAÇO - era 3rem */
}

/* Logo */
.logo {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--text-white);
    text-decoration: none;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    padding: 0.6rem 1.2rem;
    border-radius: var(--border-radius);
    transition: var(--transition-normal);
    position: relative;
    overflow: hidden;
    margin-right: 4rem; /* MUITO MAIS ESPAÇO - era 2rem */
}

.logo::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: var(--transition-normal);
}

.logo:hover::before {
    left: 100%;
}

.logo:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(92, 184, 92, 0.4);
}

/* Menu de navegação */
.nav-menu {
    display: flex;
}








@media (max-width: 1024px) {
    .nav-container {
        gap: 2rem;
        padding: 1rem 1.5rem;
    }
    
    .nav-links {
        gap: 2rem;
    }
    
    .user-actions {
        gap: 1.5rem;
    }
    
    .logo {
        margin-right: 1.5rem;
    }
}

@media (max-width: 768px) {
    .nav-container {
        gap: 1rem;
        padding: 1rem;
    }
    
    .nav-links {
        gap: 1.5rem;
    }
    
    .user-actions {
        gap: 1rem;
    }
    
    .logo {
        margin-right: 1rem;
    }
}





















.nav-links {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 5rem; /* MUITO MAIS ESPAÇO - era 3rem */
}

.nav-item {
    color: var(--text-white);
    text-decoration: none;
    font-weight: 600;
    font-size: 1rem;
    position: relative;
    padding: 0.8rem 1rem;
    border-radius: var(--border-radius);
    transition: var(--transition-normal);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.nav-item::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, var(--primary-color), var(--primary-dark));
    transition: var(--transition-normal);
    transform: translateX(-50%);
    border-radius: 2px;
}

.nav-item:hover {
    color: var(--primary-color);
    background-color: var(--background-light);
    transform: translateY(-2px);
}

.nav-item:hover::before {
    width: 80%;
}

.user-actions {
    display: flex;
    align-items: center;
    gap: 3rem; /* MUITO MAIS ESPAÇO - era 2rem */
}

/* Contador de moedas */
.coins-container {
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, rgba(255, 215, 0, 0.2), rgba(255, 215, 0, 0.1));
    backdrop-filter: blur(10px);
    padding: 0.6rem 1rem;
    border-radius: 25px;
    gap: 0.6rem;
    border: 1px solid rgba(255, 215, 0, 0.3);
    transition: var(--transition-normal);
    cursor: default;
}

.coins-container:hover {
    background: linear-gradient(135deg, rgba(255, 215, 0, 0.3), rgba(255, 215, 0, 0.2));
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(255, 215, 0, 0.2);
}

.coin-icon {
    font-size: 1.3rem;
    filter: drop-shadow(0 0 4px rgba(255, 215, 0, 0.5));
}

.coin-amount {
    font-weight: 700;
    color: var(--text-white);
    font-size: 1rem;
}

/* Botão do carrinho */
.cart-button {
    background: var(--background-light);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    width: 48px;
    height: 48px;
    cursor: pointer;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition-normal);
    backdrop-filter: blur(10px);
}

.cart-button:hover {
    background-color: var(--primary-color);
    transform: translateY(-2px);
    box-shadow: var(--shadow-heavy);
}

.cart-icon {
    font-size: 1.4rem;
}

.cart-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background-color: #e74c3c;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 0.75rem;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

/* Perfil do usuário */
.profile-wrapper {
    position: relative;
    cursor: pointer;
}

.profile-photo {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--primary-color);
    transition: var(--transition-normal);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.profile-wrapper:hover .profile-photo,
.profile-wrapper:focus .profile-photo {
    transform: scale(1.1);
    box-shadow: 0 4px 16px rgba(92, 184, 92, 0.4);
}

.profile-dropdown {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    box-shadow: var(--shadow-heavy);
    border-radius: var(--border-radius);
    width: 220px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: var(--transition-normal);
    z-index: 1001;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.profile-wrapper:hover .profile-dropdown,
.profile-wrapper:focus .profile-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.profile-info {
    padding: 1rem;
    text-align: center;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    border-radius: var(--border-radius) var(--border-radius) 0 0;
}

.profile-name {
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
}

.dropdown-separator {
    margin: 0;
    border: none;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(0, 0, 0, 0.1), transparent);
}

.profile-dropdown a {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 0.9rem 1.2rem;
    color: #333;
    text-decoration: none;
    transition: var(--transition-fast);
    font-weight: 500;
}

.profile-dropdown a:hover {
    background-color: rgba(92, 184, 92, 0.1);
    color: var(--primary-dark);
}

.logout-link {
    color: #e74c3c !important;
}

.logout-link:hover {
    background-color: rgba(231, 76, 60, 0.1) !important;
}

/* Botão de login */
.login-button {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: white;
    border: none;
    border-radius: var(--border-radius);
    padding: 0.8rem 1.5rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition-normal);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1rem;
    box-shadow: 0 2px 8px rgba(92, 184, 92, 0.3);
}

.login-button:hover {
    background: linear-gradient(135deg, var(--primary-hover), var(--primary-color));
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(92, 184, 92, 0.4);
}

/* Ícones (substitua por Font Awesome ou similar) */
.icon-shop::before { content: '🏪'; }
.icon-library::before { content: '📚'; }
.icon-user::before { content: '👤'; }
.icon-orders::before { content: '📦'; }
.icon-settings::before { content: '⚙️'; }
.icon-logout::before { content: '🚪'; }
.icon-login::before { content: '🔑'; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const profileWrapper = document.querySelector('.profile-wrapper');
    if (profileWrapper) {
        profileWrapper.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const dropdown = this.querySelector('.profile-dropdown');
                const isVisible = dropdown.style.opacity === '1';
                
                dropdown.style.opacity = isVisible ? '0' : '1';
                dropdown.style.visibility = isVisible ? 'hidden' : 'visible';
                dropdown.style.transform = isVisible ? 'translateY(-10px)' : 'translateY(0)';
                
                this.setAttribute('aria-expanded', !isVisible);
            }
        });
    }
    
    function updateCartBadge(count) {
        const badge = document.getElementById('cart-badge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
    }



        document.getElementById('cart')?.addEventListener('click', function () {
        window.location.href = "../cart.php";
    });

    
    function updateCartBadge(count) {
        const badge = document.getElementById('cart-badge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
    }
    
    });

</script>
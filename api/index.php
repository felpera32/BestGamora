<?php
// Start the session at the very beginning of the file
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Loja de Jogos</title>
  <link rel="stylesheet" href="css/styles.css">
  <script src="script.js"></script>

  <style>





.spiderman-hero {
    background-color: #0f2f6b;;
    border: 3px solid #ff6b6b;
    border-radius: 15px;
    padding: 30px;
    margin: 3% auto;
    width: 85%;
    position: relative;
    box-shadow: 0 10px 30px rgba(255, 107, 107, 0.3);
    overflow: hidden;
}

.spiderman-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255, 107, 107, 0.1) 0%, transparent 70%);
    animation: pulse 3s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 0.3; }
    50% { opacity: 0.6; }
}

.spiderman-container {
    display: flex;
    align-items: center;
    gap: 40px;
    position: relative;
    z-index: 2;
}

.spiderman-image {
    width: 733px;
    height: 400px;
    object-fit: cover;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
    transition: transform 0.3s ease;
}

.spiderman-image:hover {
    transform: scale(1.05);
}

.spiderman-details {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.spiderman-title {
    font-size: 4rem;
    font-weight: bold;
    color: #ff6b6b;
    text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.5);
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 2px;
}

.spiderman-subtitle {
    font-size: 1.5rem;
    color: #ffffff;
    margin: 0;
    opacity: 0.9;
}

.spiderman-price {
    font-size: 2.5rem;
    font-weight: bold;
    color: #7ed957;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
    margin: 10px 0;
}

.spiderman-description {
    font-size: 1.1rem;
    line-height: 1.6;
    color: #e0e0e0;
    margin: 15px 0;
}

.spiderman-btn {
    background: linear-gradient(45deg, #7ed957, #5cb342);
    color: white;
    border: none;
    padding: 15px 40px;
    font-size: 1.3rem;
    font-weight: bold;
    border-radius: 50px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(126, 217, 87, 0.4);
    text-transform: uppercase;
    letter-spacing: 1px;
}

.spiderman-btn:hover {
    background: linear-gradient(45deg, #5cb342, #4a9c35);
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(126, 217, 87, 0.6);
}

.spiderman-btn:active {
    transform: translateY(-1px);
}

.featured-badge {
    position: absolute;
    top: -10px;
    right: 30px;
    background: linear-gradient(45deg, #ff6b6b, #ff4757);
    color: white;
    padding: 10px 20px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    box-shadow: 0 4px 12px rgba(255, 107, 107, 0.4);
}









    .preco {
      color: #FFFF;
      font-size: 20px;
    }




    .rd2 {
      width: 5%;
    }
  </style>
</head>
    <?php 
         include "navbar/nav.php"
    ?>
<body>


  <main>
    <section class="produtos">
        <section class="spiderman-hero">
            <div class="featured-badge">🕷️ DESTAQUE</div>
            <div class="spiderman-container">
                <img src="https://meups.com.br/wp-content/uploads/2023/06/spider-scaled.jpg" alt="Spider-Man 2" class="spiderman-image">
                <div class="spiderman-details">
                    <h1 class="spiderman-title">Spider-Man 2</h1>
                    <p class="spiderman-subtitle">A aventura definitiva do Homem-Aranha</p>
                    <div class="spiderman-price">R$ 199,00</div>
                    <p class="spiderman-description">
                        Embarque na aventura mais emocionante de Peter Parker e Miles Morales. 
                        Explore uma Nova York vibrante, enfrente vilões icônicos e descubra 
                        o poder de ser um herói. Gráficos impressionantes e jogabilidade fluida 
                        te aguardam nesta experiência única.
                    </p>
                    <button class="spiderman-btn" onclick="redirecionarSpiderMan()">
                        🛒 Comprar Agora
                    </button>
                </div>
            </div>
        </section>


      <div class="outros-produtos">
        <div class="produto">
          <img src="https://www.outerspace.com.br/wp-content/uploads/2018/04/reddeadredemption2.jpg" class="rd2" alt="Red Dead Redemption 2">
          <h3>Red Dead Redemption 2</h3>
          <p>R$ 299,00</p>
          <div class="produto-botoes">
            <button id="comprar-reddead" onclick="redirecionarRedDead()">🛒 Comprar</button>

          </div>
        </div>

        <div class="produto">
          <img src="https://sm.ign.com/ign_pt/screenshot/default/stardew_55u8.jpg" alt="Stardew Valley" class="stardewimg">
          <h3>Stardew Valley</h3>
          <p>R$ 24,99</p>
          <div class="produto-botoes">
            <button id="comprar-stardew" onclick="redirecionarStardew()">🛒 Comprar</button>

          </div>
        </div>

        <div class="produto">
          <img src="https://i0.wp.com/nosnerds.com.br/wp-content/uploads/2018/06/sekiro-capa.jpg?resize=664%2C374&ssl=1" alt="sekiro">
          <h3>sekiro</h3>
          <p>R$ 274,00</p>
          <div class="produto-botoes">
            <button id="comprar-sekiro" onclick="redirecionarSekiro()">🛒 Comprar</button>

          </div>
        </div>

        <div class="produto">
          <img src="https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEgET7_oX5gHz-uxMD8u4rpwBN7YjlYUqaL9SYSL_ahRUaF1WHyI4JqV8_5wfPEZlBm4Lk6TNQJQ3S7IYO5erNS9T21p0FcHkLFa9gP2TAffttS6QvnvPMT3At8jkTeuLH3aH8-DQz21RO9Q/s1600/moonlighter-capa.jpg" alt="Moonlighter">
          <h3>Moonlighter</h3>
          <p>R$ 39,00</p>
          <div class="produto-botoes">
            <button id="comprar-moonlighter" onclick="redirecionarMoonlighter()">🛒 Comprar</button>

          </div>
        </div>
      </div>

      <div class="outros-produtos2">
        <div class="produto">
          <img src="https://www.oficinadanet.com.br/imagens/post/31109/capa-requisitos-minimos-para-rodar-sid-meier-s-civilization-vi-no-pc.png" alt="Civilizations 6">
          <h3>Civilizations 6</h3>
          <p>R$ 129,00</p>
          <div class="produto-botoes">
            <button id="comprar-civilizations" onclick="redirecionarCivilizations()">🛒 Comprar</button>

          </div>
        </div>

        <div class="produto">
          <img src="https://p2.trrsf.com/image/fget/cf/940/0/images.terra.com/2023/07/27/baldurs-gate-3-qhswi90megdi.jpg" alt="Baldur's Gate 3">
          <h3>Baldur's Gate 3</h3>
          <p>R$ 199,99</p>
          <div class="produto-botoes">
            <button id="comprar-bg3" onclick="redirecionarBaldursGate()">🛒 Comprar</button>

          </div>
        </div>

        <div class="produto">
          <img src="src/Capas/gta/capa.jpg" alt="GTA 6">
          <h3>GTA 6</h3>
          <p>R$ 10 MIL</p>
          <div class="produto-botoes">
            <button id="comprar-gta6" onclick="redirecionarGTA6()">🛒 Comprar</button>

          </div>
        </div>

        <div class="produto">
          <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTyvHbDoI_k79-meUcw1V0MGOW6hpERl8Ctqg&s" alt="Dave The Diver">
          <h3>Dave The Diver</h3>
          <p>R$ 59,00</p>
          <div class="produto-botoes">
            <button id="comprar-dave" onclick="redirecionarDave()">🛒 Comprar</button>

          </div>
        </div>
      </div>

      <div class="outros-produtos3">
        <div class="produto">
          <img src="https://manualdosgames.com/wp-content/uploads/2025/03/Monster-Hunter-Wilds-review-capa.jpg" alt="Monster Hunter Wilds">
          <h3>Monster Hunter Wilds</h3>
          <p>R$ 219,00</p>
          <div class="produto-botoes">
            <button id="comprar-monster" onclick="redirecionarMonster()">🛒 Comprar</button>

          </div>
        </div>

        <div class="produto">
          <img src="src/Capas/EnigmaDoMedo/capa.jpg" alt="Enigma do Medo">
          <h3>Enigma do Medo</h3>
          <p>R$ 49,50</p>
          <div class="produto-botoes">
            <button id="comprar-stardew" onclick="redirecionarEnigma()">🛒 Comprar</button>

          </div>
        </div>

        <div class="produto">
          <img src="https://www.gamerpoint.com.br/wp-content/uploads/2020/05/celeste-capa.jpg" alt="Celeste">
          <h3>Celeste</h3>
          <p>R$ 40,00</p>
          <div class="produto-botoes">
            <button id="comprar-celeste" onclick="redirecionarCeleste()">🛒 Comprar</button>

          </div>
        </div>

        <div class="produto">
          <img src="https://ultimaficha.com.br/wp-content/uploads/2024/06/Black-Myth-Wukong-capa-scaled.jpg" alt="Black Myth: Wukong">
          <h3>Black Myth: Wukong</h3>
          <p>R$ 199,99</p>
          <div class="produto-botoes">
            <button id="comprar-wukong" onclick="redirecionarWukong()">🛒 Comprar</button>

          </div>
        </div>
      </div>
    </section>
  </main>
  

  <footer>
    <p>&copy; 2025 GAMORA. Todos os direitos reservados.</p>
  </footer>
</body>

</html>
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
    /* Navbar Styles */


    /* Original styles */
    .spiderman-box {
      background-color: #0f2f6b;
      padding: 20px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      margin-left: 6%;
      width: 82%;
      margin-top: 2%;
    }

    .spiderman-image {
      width: 30%;
      border-radius: 10px;
    }

    .spiderman-detalhes {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      margin-left: 15%;
    }

    .spiderman-box h2 {
      margin-right: 33%;
      margin-top: 0%;
      font-size: 50px;
    }







    .preco {
      color: #FFFF;
      font-size: 20px;
    }

    .comprar-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 10px 20px;
      background-color: #4CAF50;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
      margin-right: 458px;
      margin-top: -62px;
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
      <div class="spiderman-box">
        <img src="src/capas/spiderman/capa.jpg" alt="Spider-Man 2" class="spiderman-image">
        <div class="spiderman-detalhes">
          <h2>Spider-Man 2</h2>
          <p class="preco">R$ 199,00</p>



          <!-- Botão de comprar -->
          <div class="buy-container">
            <button class="comprar-btn" id="spiderman-button">🛒 Comprar</button>
          </div>
        </div>
      </div>

      <div class="outros-produtos">
        <div class="produto">
          <img src="src/Capas/reddead/capa.jpg" class="rd2" alt="Red Dead Redemption 2">
          <h3>Red Dead Redemption 2</h3>
          <p>R$ 299,00</p>
          <div class="produto-botoes">
            <button id="comprar-reddead" onclick="redirecionarRedDead()">🛒 Comprar</button>

          </div>
        </div>

        <div class="produto">
          <img src="src/Capas/stardew/Capa.jpg" alt="Stardew Valley" class="stardewimg">
          <h3>Stardew Valley</h3>
          <p>R$ 24,99</p>
          <div class="produto-botoes">
            <button id="comprar-stardew" onclick="redirecionarStardew()">🛒 Comprar</button>

          </div>
        </div>

        <div class="produto">
          <img src="src/Capas/sekiro/capa.jpg" alt="sekiro">
          <h3>sekiro</h3>
          <p>R$ 274,00</p>
          <div class="produto-botoes">
            <button id="comprar-sekiro" onclick="redirecionarSekiro()">🛒 Comprar</button>

          </div>
        </div>

        <div class="produto">
          <img src="src/Capas/moonlighter/capa.jpg" alt="Moonlighter">
          <h3>Moonlighter</h3>
          <p>R$ 39,00</p>
          <div class="produto-botoes">
            <button id="comprar-moonlighter" onclick="redirecionarMoonlighter()">🛒 Comprar</button>

          </div>
        </div>
      </div>

      <div class="outros-produtos2">
        <div class="produto">
          <img src="src/Capas/civ/capa.jpg" alt="Civilizations 6">
          <h3>Civilizations 6</h3>
          <p>R$ 129,00</p>
          <div class="produto-botoes">
            <button id="comprar-civilizations" onclick="redirecionarCivilizations()">🛒 Comprar</button>

          </div>
        </div>

        <div class="produto">
          <img src="src/Capas/bg3/capa.jpg" alt="Baldur's Gate 3">
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
          <img src="src/Capas/diver/capa.jpg" alt="Dave The Diver">
          <h3>Dave The Diver</h3>
          <p>R$ 59,00</p>
          <div class="produto-botoes">
            <button id="comprar-dave" onclick="redirecionarDave()">🛒 Comprar</button>

          </div>
        </div>
      </div>

      <div class="outros-produtos3">
        <div class="produto">
          <img src="src/Capas/monster/capa.jpg" alt="Monster Hunter Wilds">
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
            <button id="comprar-stardew" onclick="redirecionarStardew()">🛒 Comprar</button>

          </div>
        </div>

        <div class="produto">
          <img src="src/capas/celeste/capa.jpg" alt="Celeste">
          <h3>Celeste</h3>
          <p>R$ 40,00</p>
          <div class="produto-botoes">
            <button id="comprar-celeste" onclick="redirecionarCeleste()">🛒 Comprar</button>

          </div>
        </div>

        <div class="produto">
          <img src="src/Capas/wukong/capa.jpg" alt="Black Myth: Wukong">
          <h3>Black Myth: Wukong</h3>
          <p>R$ 199,99</p>
          <div class="produto-botoes">
            <button id="comprar-wukong" onclick="redirecionarWukong()">🛒 Comprar</button>

          </div>
        </div>
      </div>
    </section>
  </main>
  
  <script>

  </script>

  <footer>
    <p>&copy; 2025 GAMORA. Todos os direitos reservados.</p>
  </footer>
</body>

</html>
function entrarpag() {
  window.location.href = "login.php";
};





document.addEventListener('DOMContentLoaded', function () {
  const mobileToggle = document.getElementById('mobile-toggle');
  const navMenu = document.querySelector('.nav-menu');
  const searchContainer = document.querySelector('.search-container');

  if (mobileToggle) {
    mobileToggle.addEventListener('click', function () {
      navMenu.classList.toggle('active');
      searchContainer.classList.toggle('active');
      mobileToggle.classList.toggle('active');

      if (mobileToggle.classList.contains('active')) {
        mobileToggle.querySelector('.bar:nth-child(1)').style.transform = 'rotate(45deg) translate(5px, 6px)';
        mobileToggle.querySelector('.bar:nth-child(2)').style.opacity = '0';
        mobileToggle.querySelector('.bar:nth-child(3)').style.transform = 'rotate(-45deg) translate(5px, -6px)';
      } else {
        mobileToggle.querySelector('.bar:nth-child(1)').style.transform = 'none';
        mobileToggle.querySelector('.bar:nth-child(2)').style.opacity = '1';
        mobileToggle.querySelector('.bar:nth-child(3)').style.transform = 'none';
      }
    });
  }
});






function toggleFavorite(button) {
  let heart = button.querySelector(".heart, .heart2"); 
  if (heart.classList.contains("favorito")) {
    heart.classList.remove("favorito");
    heart.innerHTML = "&#9825;"; 
  } else {
    heart.classList.add("favorito");
    heart.innerHTML = "&#9829;"; 
  }
}
document.addEventListener("DOMContentLoaded", function () {
  document.getElementById("coracao").addEventListener("click", function () {
    this.classList.toggle("red");
  });
});


document.addEventListener("DOMContentLoaded", function () {
  const coracoes = document.querySelectorAll(".coracao2");

  coracoes.forEach(function (coracao) {
    coracao.addEventListener("click", function () {
      this.classList.toggle("red");
    });
  });
});










window.addEventListener("load", adicionarEventos);




function redirecionarCart() {
  window.location.href = "cart.php"
}
function redirecionarLoja() {
  window.location.href = "index.php"
}
function redirecionarSpiderMan() {
  window.location.href = "products/spider-man.php"
}
function redirecionarRedDead() {
  window.location.href = "products/rdr2.php";
}

function redirecionarStardew() {
  window.location.href = "products/stardew.php";
}

function redirecionarSekiro() {
  window.location.href = "products/sekiro.php";
}

function redirecionarMoonlighter() {
  window.location.href = "products/moonlighter.php";
}

function redirecionarCivilizations() {
  window.location.href = "products/civilization6.php";
}

function redirecionarBaldursGate() {
  window.location.href = "products/BaldursGate.php";
}

function redirecionarGTA6() {
  window.location.href = "products/gta.php";
}

function redirecionarDave() {
  window.location.href = "products/dave.php";
}

function redirecionarMonster() {
  window.location.href = "products/monster.php";
}

function redirecionarEnigma() {
  window.location.href = "products/enigmadomedo.php";
}

function redirecionarCeleste() {
  window.location.href = "products/celeste.php";
}

function redirecionarWukong() {
  window.location.href = "products/wukong.php";
}

// add evento no botão
function adicionarEventos() {
  document.getElementById('comprar-reddead').addEventListener('click', redirecionarRedDead);
  document.getElementById('comprar-stardew').addEventListener('click', redirecionarStardew);
  document.getElementById('comprar-sekiro').addEventListener('click', redirecionarSekiro);
  document.getElementById('comprar-moonlighter').addEventListener('click', redirecionarMoonlighter);
  document.getElementById('comprar-civilizations').addEventListener('click', redirecionarCivilizations);
  document.getElementById('comprar-bg3').addEventListener('click', redirecionarBaldursGate);
  document.getElementById('comprar-gta6').addEventListener('click', redirecionarGTA6);
  document.getElementById('comprar-dave').addEventListener('click', redirecionarDave);
  document.getElementById('comprar-monster').addEventListener('click', redirecionarMonster);
  document.getElementById('comprar-enigma').addEventListener('click', redirecionarEnigma);
  document.getElementById('comprar-celeste').addEventListener('click', redirecionarCeleste);
  document.getElementById('comprar-wukong').addEventListener('click', redirecionarWukong);
  document.getElementById('spiderman-button').addEventListener('click', redirecionarSpiderMan);
  document.getElementById('cart').addEventListener('click', redirecionarCart);
}

window.onload = adicionarEventos;
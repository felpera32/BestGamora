<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Publicar Jogo - GAMORA</title>
  <link rel="stylesheet" href="css/seller.css">
  <script src="script.js"></script>

</head>

<?php 
     include "navbar/sellernav.php"
?>

<body>
  <div class="container">
    <h1>Publicar Novo Jogo</h1>
    
    <form action="processar_publicacao.php" method="POST" enctype="multipart/form-data">
      <!-- Nome e Preço -->
      <div class="form-row">
        <div class="form-group">
          <label for="game-name">Nome do Jogo</label>
          <input type="text" id="game-name" name="game_name" placeholder="Digite o nome do jogo" required>
        </div>
        
        <div class="form-group">
          <label for="price">Preço (R$)</label>
          <input type="number" id="price" name="price" step="0.01" min="0" placeholder="Digite o preço do jogo" required>
        </div>
      </div>
      
      <!-- Desenvolvedor e Publicador -->
      <div class="form-row">
        <div class="form-group">
          <label for="developer">Desenvolvedor</label>
          <input type="text" id="developer" name="developer" placeholder="Nome do desenvolvedor ou estúdio">
        </div>
        
        <div class="form-group">
          <label for="publisher">Publicador</label>
          <input type="text" id="publisher" name="publisher" placeholder="Nome da publicadora">
        </div>
      </div>
      
      <!-- Data e Categoria -->
      <div class="form-row">
        <div class="form-group">
          <label for="release-date">Data de Lançamento</label>
          <input type="date" id="release-date" name="release_date">
        </div>
        
        <div class="form-group">
          <label for="category">Categoria</label>
          <select id="category" name="category">
            <option value="">Selecione uma categoria</option>
            <option value="acao">Ação</option>
            <option value="aventura">Aventura</option>
            <option value="rpg">RPG</option>
            <option value="estrategia">Estratégia</option>
            <option value="simulacao">Simulação</option>
            <option value="esportes">Esportes</option>
            <option value="corrida">Corrida</option>
            <option value="puzzle">Puzzle</option>
            <option value="indie">Indie</option>
            <option value="outros">Outros</option>
          </select>
        </div>
      </div>
      
      <!-- Descrição -->
      <div class="form-group">
        <label for="description">Descrição do Jogo</label>
        <textarea id="description" name="description" placeholder="Descreva o jogo com detalhes" required></textarea>
      </div>
      
      <!-- Requisitos e Características -->
      <div class="form-row">
        <div class="form-group">
          <label for="system-requirements">Requisitos de Sistema</label>
          <textarea id="system-requirements" name="system_requirements" placeholder="Liste os requisitos mínimos e recomendados"></textarea>
        </div>
        
        <div class="form-group">
          <label for="gameplay-features">Características de Gameplay</label>
          <textarea id="gameplay-features" name="gameplay_features" placeholder="Descreva as principais características e mecânicas do jogo"></textarea>
        </div>
      </div>
      
      <!-- Tags -->
      <div class="form-group">
        <label for="tags-input">Tags (Aperte Enter para adicionar)</label>
        <input type="text" id="tags-input" placeholder="Ex: multijogador, fantasia, primeira pessoa">
        <div class="tags-container" id="tags-container"></div>
        <input type="hidden" id="tags-hidden" name="tags">
      </div>
      
      <!-- Uploads Section -->
      <div class="uploads-section">
        <div class="upload-group">
          <label for="cover-image">Imagem de Capa</label>
          <div class="upload-container" id="cover-image-upload">
            <div class="upload-icon">📷</div>
            <p class="upload-text">Clique ou arraste para adicionar a imagem de capa</p>
            <input type="file" class="hidden-input" id="cover-image" name="cover_image" accept="image/*">
          </div>
          <div class="preview-container" id="cover-preview"></div>
        </div>
        
        <div class="upload-group">
          <label for="screenshots">Screenshots (Até 4 imagens)</label>
          <div class="upload-container" id="screenshots-upload">
            <div class="upload-icon">🖼️</div>
            <p class="upload-text">Clique ou arraste para adicionar screenshots</p>
            <input type="file" class="hidden-input" id="screenshots" name="screenshots[]" accept="image/*" multiple>
          </div>
          <div class="preview-container" id="screenshots-preview"></div>
        </div>
      </div>
      
      <button type="submit" class="btn-submit">Publicar Jogo</button>
    </form>
  </div>

  <footer>
    <p>&copy; 2025 GAMORA. Todos os direitos reservados.</p>
  </footer>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Imagem de capa
      const coverUpload = document.getElementById('cover-image-upload');
      const coverInput = document.getElementById('cover-image');
      const coverPreview = document.getElementById('cover-preview');
      
      coverUpload.addEventListener('click', () => {
        coverInput.click();
      });
      
      coverInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
          const reader = new FileReader();
          reader.onload = function(e) {
            coverPreview.innerHTML = `<img src="${e.target.result}" style="max-width:100%; max-height:200px; border-radius:6px;">`;
          };
          reader.readAsDataURL(this.files[0]);
        }
      });
      
      // Screenshots
      const screenshotsUpload = document.getElementById('screenshots-upload');
      const screenshotsInput = document.getElementById('screenshots');
      const screenshotsPreview = document.getElementById('screenshots-preview');
      
      screenshotsUpload.addEventListener('click', () => {
        screenshotsInput.click();
      });
      
      screenshotsInput.addEventListener('change', function() {
        screenshotsPreview.innerHTML = '';
        if (this.files && this.files.length > 0) {
          const maxFiles = Math.min(this.files.length, 5);
          
          for (let i = 0; i < maxFiles; i++) {
            const file = this.files[i];
            const reader = new FileReader();
            
            reader.onload = function(e) {
              const imgContainer = document.createElement('div');
              imgContainer.style.width = '120px';
              imgContainer.style.height = '80px';
              imgContainer.style.overflow = 'hidden';
              imgContainer.style.borderRadius = '6px';
              imgContainer.style.position = 'relative';
              
              const img = document.createElement('img');
              img.src = e.target.result;
              img.style.width = '100%';
              img.style.height = '100%';
              img.style.objectFit = 'cover';
              
              imgContainer.appendChild(img);
              screenshotsPreview.appendChild(imgContainer);
            };
            
            reader.readAsDataURL(file);
          }
        }
      });
      
      // Tags system
      const tagsInput = document.getElementById('tags-input');
      const tagsContainer = document.getElementById('tags-container');
      const tagsHidden = document.getElementById('tags-hidden');
      const tags = [];
      
      function updateTags() {
        tagsContainer.innerHTML = '';
        tagsHidden.value = JSON.stringify(tags);
        
        tags.forEach((tag, index) => {
          const tagElement = document.createElement('div');
          tagElement.className = 'tag';
          tagElement.innerHTML = `
            <span>${tag}</span>
            <button type="button" data-index="${index}">×</button>
          `;
          tagElement.querySelector('button').addEventListener('click', function() {
            const index = parseInt(this.getAttribute('data-index'));
            tags.splice(index, 1);
            updateTags();
          });
          
          tagsContainer.appendChild(tagElement);
        });
      }
      
      tagsInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && this.value.trim() !== '') {
          e.preventDefault();
          const tag = this.value.trim();
          if (!tags.includes(tag)) {
            tags.push(tag);
            updateTags();
          }
          this.value = '';
        }
      });
      
      // func de puxar e colocar img
      ['cover-image-upload', 'screenshots-upload'].forEach(id => {
        const element = document.getElementById(id);
        
        element.addEventListener('dragover', e => {
          e.preventDefault();
          element.style.backgroundColor = 'rgba(255, 255, 255, 0.15)';
          element.style.borderColor = '#5cb85c';
        });
        
        element.addEventListener('dragleave', e => {
          e.preventDefault();
          element.style.backgroundColor = 'rgba(255, 255, 255, 0.05)';
          element.style.borderColor = 'rgba(255, 255, 255, 0.3)';
        });
        
        element.addEventListener('drop', e => {
          e.preventDefault();
          element.style.backgroundColor = 'rgba(255, 255, 255, 0.05)';
          element.style.borderColor = 'rgba(255, 255, 255, 0.3)';
          
          const files = e.dataTransfer.files;
          if (files.length) {
            if (id === 'cover-image-upload') {
              document.getElementById('cover-image').files = files;
              const event = new Event('change');
              document.getElementById('cover-image').dispatchEvent(event);
            } else {
              document.getElementById('screenshots').files = files;
              const event = new Event('change');
              document.getElementById('screenshots').dispatchEvent(event);
            }
          }
        });
      });
    });
  </script>
</body>

</html>
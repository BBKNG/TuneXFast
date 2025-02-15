<?php
// index.php
session_start();

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Redirection vers la page de connexion si non connecté
    header('Location: login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>TuneXFast - Calculateur de Devis</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
  <style>
    body {
      background-color: #000; /* Fond noir */
      color: #fff;
    }
    nav strong {
      color: white;
    }
    nav strong span {
      color: #c8a700; /* "X" en doré */
    }
    select, input, button {
      color: #fff;
      background-color: #222;
      border: 1px solid #c8a700;
    }
    option {
      color: #fff;
      background-color: #222;
    }
    .light-theme {
      background-color: #fff;
      color: #000;
    }
    #qrcode {
      margin-top: 20px;
    }
  </style>
  <!-- Librairies externes (PDF, QRCode) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <script>
    // Liste des marques et modèles
    const modelsByBrand = {
      "Honda": ["CBR1000RR-R Fireblade", "CBR650R", "CBR500R", "CBR300R", "CBR250R", "CBR150R", "CBR125R", "CB1000R", "CB500F", "Africa Twin", "Forza 750", "PCX 125", "Monkey 125", "CB125R"],
      "Yamaha": ["YZF-R1M", "YZF-R1", "YZF-R9", "YZF-R7", "YZF-R3", "YZF-R125", "MT-10 SP", "MT-09", "Ténéré 700", "Tracer 9", "XMAX 125", "NMAX 125"],
      "Kawasaki": ["Ninja ZX-10R", "Ninja ZX-6R", "Ninja 400", "Z900", "Z650", "Versys 650", "J300", "Z125"],
      "Suzuki": ["Hayabusa", "GSX-R1000", "GSX-R750", "GSX-R600", "V-Strom 650", "Burgman 400", "GSX-S125"],
      "SYM": ["JET14", "Fiddle", "Crox", "Joymax", "Orbit", "Wolf 125", "NH-T 125"],
      "Zontes": ["125 GK", "125 U", "350 X", "350 D", "703RR"],
      "KTM": ["RC 125", "RC 390", "390 Duke", "1290 Super Adventure R", "790 Duke", "890 Duke"],
      "Vespa": ["Primavera 50", "Primavera 125", "Sprint 50", "Sprint 125", "GTS 125", "GTS 300", "Sei Giorni"],
      "Piaggio": ["Liberty 50", "Liberty 125", "Beverly 300", "MP3 400", "Zip 50", "NRG 50"],
      "Peugeot": ["Django 50", "Django 125", "Speedfight 50", "Tweet 125", "Kisbee 50", "Metropolis 400"]
    };

    function updateModels() {
      const brand = document.getElementById("brand").value;
      const modelSelect = document.getElementById("model");
      modelSelect.innerHTML = "<option value='' disabled selected>Choisissez un modèle</option>";
      if (modelsByBrand[brand]) {
        modelsByBrand[brand].forEach(model => {
          const option = document.createElement("option");
          option.value = model;
          option.textContent = model;
          modelSelect.appendChild(option);
        });
        modelSelect.disabled = false;
      } else {
        modelSelect.disabled = true;
      }
    }

    // Mise à jour du total pour les services sélectionnés
    function updateTotal() {
      let total = 0;
      document.querySelectorAll('.service:checked').forEach(el => {
        total += parseFloat(el.getAttribute('data-prix'));
      });
      document.getElementById("total").textContent = `Total : ${total}€`;
      document.getElementById("hiddenTotal").value = total; // Pour envoyer en POST
    }

    // Génération du QR Code pour partager le devis
    document.addEventListener('DOMContentLoaded', function() {
      new QRCode(document.getElementById("qrcode"), {
        text: "https://votresite.com/devis/12345",
        width: 128,
        height: 128,
      });
    });

    // Fonction pour générer une facture PDF
    async function generatePDF() {
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF();
      doc.text("Facture TuneXFast", 10, 10);

      const brand = document.getElementById("brand").value || "Non spécifié";
      const model = document.getElementById("model").value || "Non spécifié";
      const total = document.getElementById("total").textContent || "Total : 0€";

      doc.text(`Marque : ${brand}`, 10, 20);
      doc.text(`Modèle : ${model}`, 10, 30);
      doc.text(total, 10, 40);

      doc.save("facture.pdf");
    }

    // Bascule du mode clair/sombre
    document.addEventListener("DOMContentLoaded", () => {
      const toggleTheme = document.getElementById('toggleTheme');
      toggleTheme.addEventListener('click', () => {
        document.body.classList.toggle('light-theme');
      });
    });
  </script>
</head>
<body>
  <nav class="container-fluid">
    <ul>
      <li><strong>Tune<span>X</span>Fast</strong></li>
      <li><button id="toggleTheme" type="button">Mode Clair/Sombre</button></li>
    </ul>
  </nav>

  <main class="container">
    <hgroup>
      <h2>Calculateur de Devis</h2>
      <h3>Choisissez votre véhicule et les services désirés.</h3>
    </hgroup>

    <form method="post" action="process.php">
      <fieldset>
        <legend>Votre véhicule</legend>
        <label for="brand">Marque</label>
        <select id="brand" name="brand" onchange="updateModels()">
          <option value="" disabled selected>Choisissez une marque</option>
          <script>
            Object.keys(modelsByBrand).forEach(brand => {
              document.write(`<option value="${brand}">${brand}</option>`);
            });
          </script>
        </select>

        <label for="model">Modèle</label>
        <select id="model" name="model" disabled>
          <option value="" disabled selected>Choisissez une marque d'abord</option>
        </select>
      </fieldset>

      <fieldset>
        <legend>Services</legend>
        <label><input type="checkbox" class="service" data-prix="50" name="service[]" value="vidange" onchange="updateTotal()"> Vidange (50€)</label>
        <label><input type="checkbox" class="service" data-prix="30" name="service[]" value="plaquettes" onchange="updateTotal()"> Remplacement de plaquettes (30€)</label>
        <label><input type="checkbox" class="service" data-prix="60" name="service[]" value="kit_chaine" onchange="updateTotal()"> Remplacement kit chaîne (60€)</label>
        <label><input type="checkbox" class="service" data-prix="40" name="service[]" value="purge_frein" onchange="updateTotal()"> Purge circuit de frein (40€)</label>
        <label><input type="checkbox" class="service" data-prix="80" name="service[]" value="revision_generale" onchange="updateTotal()"> Révision générale (80€)</label>
        <label><input type="checkbox" class="service" data-prix="35" name="service[]" value="filtre_air" onchange="updateTotal()"> Remplacement filtre à air (35€)</label>
        <label><input type="checkbox" class="service" data-prix="25" name="service[]" value="bougies" onchange="updateTotal()"> Remplacement bougies (25€)</label>
      </fieldset>

      <p id="total">Total : 0€</p>
      <!-- Champ caché pour transmettre le total en POST -->
      <input type="hidden" id="hiddenTotal" name="total" value="0">

      <button type="button" onclick="generatePDF()">Générer Facture PDF</button>
      <button type="submit" name="submit">Valider le devis</button>
    </form>

    <section id="qrcode"></section>
  </main>

  <footer class="container">
    <small>
      <a href="#">Mentions légales</a> • <a href="#">Confidentialité</a>
    </small>
  </footer>
</body>
</html>

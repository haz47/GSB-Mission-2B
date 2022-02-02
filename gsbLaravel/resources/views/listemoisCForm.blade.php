@extends ('listemoisC')
    @section('contenu2')
      <div id="contenu">
        <h2>Mes fiches de frais</h2>
        <h3>Mois à sélectionner : </h3>
          <form method="POST" action="{{ route('chemin_updateFrais') }}">
            {{ csrf_field() }} <!-- laravel va ajouter un champ caché avec un token -->
          <table border = 1px>
                <tr>
                    <th>Selection</th>
                    <th>Visiteurs</th>
                    <th>Mois</th>
                    <th>Montant</th>
                    <th>Statut</th>
                </tr>
            <?php foreach($lesRembourses as $rembourse){ ?>
                <tr>
                    <td><input type="checkbox" id="" name="lstrembourse" value="<?= $rembourse['idVisiteur']." ".$rembourse['mois']?>"></td>
                    <td><?= $rembourse['idVisiteur']; ?></td>
                    <td><?= $rembourse['mois']; ?></td>
                    <td><?= $rembourse['montantValide']; ?></td>
                    <td><?= $rembourse['idEtat']; ?></td>
                </tr>
            <?php } ?>
            </table>
            <div class="piedForm">
              <p>
                <input id="ok" type="submit" value="Valider" size="20" />
                <input id="annuler" type="reset" value="Effacer" size="20" />
              </p> 
          </div>
          </form>
@endsection 
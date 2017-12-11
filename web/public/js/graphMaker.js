/**
  CREATION DE DIAGRAMME ET GRAPHIQUES AVEC API GOOGLE CHARTS.
**/


/**
  FONCTION QUI appelle les methodes de dessin en fonction du contenu de la page.
**/
function DrawAllCharts(){
    var Questions = $(document.getElementsByName('valref'));    //obtention des questions avec Name="valref"
    $.each(Questions, function(i,DivQuest){                     //pour chacune de ces questions
      var p_info = $(DivQuest).find('p');
      var $infoQuest = $(p_info).attr('type');
      var $infoQuest = $infoQuest.split(';');
      var $typeGraph = $infoQuest[0];                           //le type de graphe souhaité
      var $reference = $infoQuest[1];                           //la référence
      var valeur = $(DivQuest).text();

      //console.log($reference+' : '+$typeGraph);
      valeur = JSON.parse(valeur);                              //on decode la chaine JSON des résultats.
      var Tab_for_graph = [];
      $.each(valeur, function(i,value){                         //on transforme en un Array de Array(reponse,nb)
        Tab_for_graph.push([i,value]);
      });



      if($typeGraph=="NONE"){                                   //Si le type de graphique est NONE
        DivQuest.append('');                                    //pas de graphique à afficher
      }
      else if(Tab_for_graph.length==0){                         //si il n'y a aucune valeur de références, on le dit
        $(DivQuest).html('Aucune valeur de référence trouvé pour : <em>'+$reference+'</em><br/>');
      }
      else if($typeGraph=="column"){                            //le type de graphique est column, diagramme en baton
        google.charts.setOnLoadCallback(drawChartColumn(DivQuest,Tab_for_graph));
      }
      else if($typeGraph=="donut"){                             //le type de graphique est donut, camembert.
        google.charts.setOnLoadCallback(drawChartDonut(DivQuest,Tab_for_graph));
      }

      $(DivQuest).prepend('<li>Réponse participant : à définir.</li><br/>');//la reponses à la question du participant

    });
}


/**
  Dessine un diagramme en bâton dans la div 'LaBonneDiv' à parti du tableau de données 'ArrayValue'
**/
function drawChartColumn(LaBonneDiv,ArrayValue) {
  var data = new google.visualization.DataTable();
  data.addColumn('string','Réponses');
  data.addColumn('number','Valeurs');
  data.addRows(ArrayValue);
	var options = {
		width: 600,
		height: 400,
		bar: {groupWidth: "50%"},
		legend: { position: "none" },
	};
	var chart = new google.visualization.ColumnChart(LaBonneDiv);
	chart.draw(data, options);
};
/**
  Dessine un diagramme en camembert dans la div 'LaBonneDiv' à parti du tableau de données 'ArrayValue'
**/
function drawChartDonut(LaBonneDiv,ArrayValue) {
	// Create the data table.
	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Réponses');
	data.addColumn('number', 'Valeurs');

	data.addRows(ArrayValue);
	// Set chart options
	var options = {
								 'pieHole':0.4,
								 'legend': 'none',
								 'pieSliceText': 'label',
								 'width':500,
								 'height':500,
								 'slices': [{color: '#B9121B'}, {color: '#DBEA01'}, {color: 'orange'}, {color: 'green'}]
							 };
	// Instantiate and draw our chart, passing in some options.
	var chart = new google.visualization.PieChart(LaBonneDiv);
	chart.draw(data, options);
};


//FONCTION MAIN. appelée après chargement complet de la page
window.onload = function(){
  google.charts.load("current", {packages:['corechart']});        //CHARGEMENT DES PACKAGES DE GRAPHIQUE

  google.charts.setOnLoadCallback(DrawAllCharts);                 //DESSIN DES GRAPHIQUES
;
}

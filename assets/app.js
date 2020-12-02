/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
import $ from 'jquery';
import 'bootstrap';


$('.custom-file-input').on('change',function(e){
  //on stock dans une variable ce que l on recupere notre input
  var inputFile = e.currentTarget;
  // console.log(inputFile.files[0].name)
  //on vient au niveau du parent de l'input,on recherche avec find un enfant avec la class custom-file-label et on modifie son contenu html avec le nom de notre fichier 
  $(inputFile).parent().find('.custom-file-label').html(inputFile.files[0].name);
})


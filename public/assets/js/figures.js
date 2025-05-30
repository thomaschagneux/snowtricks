$(function () {
  loadFigures()
  function loadFigures() {
    console.log('test1')
    $.ajax({
      url: '/figure/ajax-all',
      type: 'GET',
      dataType: 'json',
      success: function (data) {
        console.log(data); // Vérifiez la structure des données retournées

        let figuresContainer = $('.js-figures-container');
        figuresContainer.empty();
        let figureRequests = data.map(function (figure) {
          const $ajax = $.ajax({
            url: '/figure/' + figure.slug + '/ajax-one',
            type: 'GET'
          });
          console.log(figure)
          return $ajax
        });

        Promise.all(figureRequests).then(function (figureHtmls) {
          figureHtmls.forEach(function (figureHtml) {
            figuresContainer.append(figureHtml);
          });

          if (data.length < 15) {
            $('#load-more-figures').fadeOut();
          }
        }).catch(function (error) {
          console.error(error);
        });
      },
      error: function (xhr, status, error) {
        console.error(error);
      }
    });
  }
})

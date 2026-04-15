/* Keen Training — Admin JS */
(function ($) {
	'use strict';

	/* Toggle formulário inline de edição de módulo */
	window.ktToggleForm = function (id) {
		var row = document.getElementById(id);
		if (row) row.style.display = (row.style.display === 'none' ? 'table-row' : 'none');
	};

	/* Toggle alvo da matrícula: colaboradores ↔ unidade */
	$(document).on('change', 'input[name="target_type"]', function () {
		if ($(this).val() === 'location') {
			$('#kt-members-row').hide();
			$('#kt-location-row').show();
		} else {
			$('#kt-members-row').show();
			$('#kt-location-row').hide();
		}
	});

	/* -----------------------------------------------------------------------
	 * Editor de perguntas da avaliação
	 * -------------------------------------------------------------------- */
	var qIndex = $('#kt-questions-container .kt-question-block').length;

	function buildAnswer(qi, ai) {
		return '<div class="kt-answer-row">' +
			'<input type="checkbox" name="questions[' + qi + '][answers][' + ai + '][is_correct]" value="1" title="Marcar como correta">' +
			'<input type="text" name="questions[' + qi + '][answers][' + ai + '][text]" class="regular-text" placeholder="Alternativa">' +
			'<button type="button" class="button-link kt-delete-link kt-remove-answer">✕</button>' +
		'</div>';
	}

	function buildQuestion(qi) {
		return '<div class="kt-question-block" data-index="' + qi + '">' +
			'<div class="kt-question-header">' +
				'<strong>Pergunta ' + (qi + 1) + '</strong>' +
				'<div>' +
					'<select name="questions[' + qi + '][question_type]" style="margin-right:8px">' +
						'<option value="multiple_choice">Múltipla Escolha</option>' +
						'<option value="true_false">Verdadeiro / Falso</option>' +
					'</select>' +
					'<button type="button" class="button-link kt-delete-link kt-remove-question">Remover</button>' +
				'</div>' +
			'</div>' +
			'<p><label>Texto da Pergunta<br>' +
			'<textarea name="questions[' + qi + '][question_text]" class="large-text" rows="2" required></textarea>' +
			'</label></p>' +
			'<div class="kt-answers">' +
				'<p><strong>Alternativas</strong> <small style="color:#888">(marque a correta)</small></p>' +
				buildAnswer(qi, 0) +
				buildAnswer(qi, 1) +
				'<button type="button" class="button kt-add-answer">+ Alternativa</button>' +
			'</div>' +
		'</div>';
	}

	$(document).on('click', '#kt-add-question', function () {
		$('#kt-questions-container').append(buildQuestion(qIndex));
		qIndex++;
	});

	$(document).on('click', '.kt-remove-question', function () {
		$(this).closest('.kt-question-block').remove();
	});

	$(document).on('click', '.kt-add-answer', function () {
		var block = $(this).closest('.kt-question-block');
		var qi    = block.data('index');
		var ai    = block.find('.kt-answer-row').length;
		$(this).before(buildAnswer(qi, ai));
	});

	$(document).on('click', '.kt-remove-answer', function () {
		$(this).closest('.kt-answer-row').remove();
	});

})(jQuery);

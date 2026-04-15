/* Keen Training — Portal do Colaborador JS */
(function ($) {
	'use strict';

	if (typeof ktFrontend === 'undefined') return;

	/* -----------------------------------------------------------------------
	 * Destacar alternativa selecionada
	 * -------------------------------------------------------------------- */
	$(document).on('change', '.kt-response-input', function () {
		var qid = $(this).data('question-id');
		$('[data-question-id="' + qid + '"]').closest('.kt-answer-option').removeClass('selected');
		$(this).closest('.kt-answer-option').addClass('selected');
		// Remove indicação de não respondida
		$(this).closest('.kt-question').removeClass('kt-unanswered');
	});

	/* -----------------------------------------------------------------------
	 * Marcar módulo como concluído
	 * -------------------------------------------------------------------- */
	$(document).on('click', '.kt-complete-module', function () {
		var $btn     = $(this);
		var moduleId = $btn.data('module-id');

		$btn.prop('disabled', true).text(ktFrontend.i18n.salvando);

		$.post(ktFrontend.ajaxUrl, {
			action:    'kt_complete_module',
			nonce:     ktFrontend.nonce,
			module_id: moduleId
		})
		.done(function (resp) {
			if (resp.success) {
				var pct = resp.data.progress;

				// Atualiza a aparência do módulo
				var $item = $btn.closest('.kt-module-item');
				$item.addClass('kt-module-complete');
				$btn.replaceWith('<span class="kt-module-done-label">✓ ' + ktFrontend.i18n.concluido + '</span>');
				$item.find('.kt-module-number').css({background: '#22c55e', color: '#fff'}).text('✓');
				$item.find('.kt-module-status-badge').html('<span class="kt-badge kt-badge-success">Concluído</span>');

				// Atualiza a barra de progresso do topo
				$('#kt-course-progress-bar').css('width', pct + '%');
				$('#kt-course-progress-label').text(pct + '% concluído');

				// Exibe banner se curso 100%
				if (resp.data.course_done || pct >= 100) {
					$('#kt-completion-banner').show();
					$('html, body').animate({ scrollTop: $('#kt-completion-banner').offset().top - 80 }, 500);
				}
			} else {
				$btn.prop('disabled', false).text('✔ Marcar como Concluído');
				var msg = resp.data && resp.data.message ? resp.data.message : ktFrontend.i18n.erro_rede;
				showAlert(msg, 'fail');
			}
		})
		.fail(function () {
			$btn.prop('disabled', false).text('✔ Marcar como Concluído');
			showAlert(ktFrontend.i18n.erro_rede, 'fail');
		});
	});

	/* -----------------------------------------------------------------------
	 * Submeter avaliação
	 * -------------------------------------------------------------------- */
	$(document).on('submit', '#kt-quiz-form', function (e) {
		e.preventDefault();

		var quizId   = $('#kt-quiz-id').val();
		var moduleId = $('#kt-module-id').val();
		var responses = {};

		$('.kt-response-input:checked').each(function () {
			responses[$(this).data('question-id')] = $(this).val();
		});

		// Valida que todas as perguntas foram respondidas
		var allAnswered = true;
		$('.kt-question').each(function () {
			var qid = $(this).data('question-id');
			if (!responses[qid]) {
				allAnswered = false;
				$(this).addClass('kt-unanswered');
				$(this).find('.kt-answer-option').first().closest('.kt-answers').before(
					'<p style="color:#b91c1c;font-size:.85em;margin:4px 0">⚠ Responda esta pergunta.</p>'
				);
			}
		});

		if (!allAnswered) {
			$('html, body').animate({ scrollTop: $('.kt-unanswered').first().offset().top - 100 }, 400);
			return;
		}

		var $btn = $('#kt-quiz-submit');
		$btn.prop('disabled', true).text(ktFrontend.i18n.enviando);
		$('.kt-unanswered').removeClass('kt-unanswered');
		$('.kt-question p[style]').remove();

		$.post(ktFrontend.ajaxUrl, {
			action:    'kt_submit_quiz',
			nonce:     ktFrontend.nonce,
			quiz_id:   quizId,
			module_id: moduleId,
			responses: responses
		})
		.done(function (resp) {
			$btn.prop('disabled', false).text('Enviar Avaliação');
			var $result = $('#kt-quiz-result');

			if (resp.success) {
				var d = resp.data;
				var html = '<p style="font-size:1.15em">' + d.message + '</p>' +
					'<p>Acertos: <strong>' + d.correct + ' de ' + d.total + '</strong></p>';

				if (d.passed) {
					$('#kt-quiz-form').hide();
				} else {
					var ilimitado = (d.tentativas_restantes === -1);
					if (ilimitado || d.tentativas_restantes > 0) {
						if (!ilimitado) {
							html += '<p>Tentativas restantes: <strong>' + d.tentativas_restantes + '</strong></p>';
						}
						html += '<button type="button" class="kt-btn" id="kt-retry-btn">' + ktFrontend.i18n.tentar_nov + '</button>';
					} else {
						html += '<p>Você não tem mais tentativas disponíveis. Fale com seu gerente.</p>';
					}
				}

				$result
					.removeClass('kt-result-pass kt-result-fail')
					.addClass(d.passed ? 'kt-result-pass' : 'kt-result-fail')
					.html(html)
					.show();

				$('html, body').animate({ scrollTop: $result.offset().top - 80 }, 400);
			} else {
				var msg = resp.data && resp.data.message ? resp.data.message : ktFrontend.i18n.erro_rede;
				$result.removeClass('kt-result-pass kt-result-fail')
					.addClass('kt-result-fail').html('<p>' + msg + '</p>').show();
			}
		})
		.fail(function () {
			$btn.prop('disabled', false).text('Enviar Avaliação');
			showAlert(ktFrontend.i18n.erro_rede, 'fail');
		});
	});

	/* -----------------------------------------------------------------------
	 * Shortcode [kt_quiz] — formulário embed em páginas Elementor
	 * -------------------------------------------------------------------- */
	$(document).on('submit', '.kt-quiz-embed-form', function (e) {
		e.preventDefault();

		var $form    = $(this);
		var quizId   = $form.find('.kt-quiz-id-input').val();
		var moduleId = $form.find('.kt-module-id-input').val();
		var $result  = $('#kt-quiz-result-' + quizId);
		var $submit  = $form.find('.kt-quiz-embed-submit');
		var responses = {};

		$form.find('.kt-response-input:checked').each(function () {
			responses[$(this).data('question-id')] = $(this).val();
		});

		// Valida perguntas não respondidas
		var allAnswered = true;
		$form.find('.kt-question').each(function () {
			var qid = $(this).data('question-id');
			if (!responses[qid]) {
				allAnswered = false;
				$(this).addClass('kt-unanswered');
				if (!$(this).find('.kt-unanswered-msg').length) {
					$(this).find('.kt-answers').before('<p class="kt-unanswered-msg" style="color:#b91c1c;font-size:.85em;margin:4px 0">⚠ Responda esta pergunta.</p>');
				}
			} else {
				$(this).removeClass('kt-unanswered');
				$(this).find('.kt-unanswered-msg').remove();
			}
		});

		if (!allAnswered) {
			$('html, body').animate({ scrollTop: $form.find('.kt-unanswered').first().offset().top - 100 }, 400);
			return;
		}

		$submit.prop('disabled', true).text(ktFrontend.i18n.enviando);

		$.post(ktFrontend.ajaxUrl, {
			action:    'kt_submit_quiz',
			nonce:     ktFrontend.nonce,
			quiz_id:   quizId,
			module_id: moduleId,
			responses: responses
		})
		.done(function (resp) {
			$submit.prop('disabled', false).text('Enviar Avaliação');
			if (resp.success) {
				var d = resp.data;
				var html = '<p style="font-size:1.1em">' + d.message + '</p>' +
					'<p>Acertos: <strong>' + d.correct + ' de ' + d.total + '</strong></p>';

				if (d.passed) {
					$form.slideUp(300);
				} else {
					var ilimitado = (d.tentativas_restantes === -1);
					if (ilimitado || d.tentativas_restantes > 0) {
						if (!ilimitado) {
							html += '<p>Tentativas restantes: <strong>' + d.tentativas_restantes + '</strong></p>';
						}
						html += '<button type="button" class="kt-btn kt-embed-retry-btn" data-quiz-id="' + quizId + '">' + ktFrontend.i18n.tentar_nov + '</button>';
					} else {
						html += '<p>Você não tem mais tentativas disponíveis. Fale com seu gerente.</p>';
					}
				}

				$result
					.removeClass('kt-result-pass kt-result-fail')
					.addClass(d.passed ? 'kt-result-pass' : 'kt-result-fail')
					.html(html).show();

				$('html, body').animate({ scrollTop: $result.offset().top - 80 }, 400);
			} else {
				var msg = resp.data && resp.data.message ? resp.data.message : ktFrontend.i18n.erro_rede;
				$result.removeClass('kt-result-pass kt-result-fail')
					.addClass('kt-result-fail').html('<p>' + msg + '</p>').show();
			}
		})
		.fail(function () {
			$submit.prop('disabled', false).text('Enviar Avaliação');
			$result.addClass('kt-result-fail').html('<p>' + ktFrontend.i18n.erro_rede + '</p>').show();
		});
	});

	/* Botão tentar novamente (embed) */
	$(document).on('click', '.kt-embed-retry-btn', function () {
		var $embed = $(this).closest('.kt-quiz-embed');
		var quizId = $(this).data('quiz-id');
		$('#kt-quiz-result-' + quizId).hide();
		$embed.find('.kt-quiz-embed-form').show();
		$embed.find('.kt-answer-option').removeClass('selected');
		$embed.find('.kt-response-input').prop('checked', false);
		$embed.find('.kt-unanswered').removeClass('kt-unanswered');
		$embed.find('.kt-unanswered-msg').remove();
		$('html, body').animate({ scrollTop: $embed.offset().top - 80 }, 400);
	});

	/* Botão tentar novamente */
	$(document).on('click', '#kt-retry-btn', function () {
		$('#kt-quiz-result').hide();
		$('#kt-quiz-form').show();
		$('.kt-answer-option').removeClass('selected');
		$('.kt-response-input').prop('checked', false);
		$('html, body').animate({ scrollTop: $('#kt-quiz-form').offset().top - 80 }, 400);
	});

	/* Helper: alerta embutido */
	function showAlert(msg, type) {
		var cls   = type === 'fail' ? 'kt-result-fail' : 'kt-result-pass';
		var $alert = $('<div class="kt-quiz-result ' + cls + '" style="margin:16px 0"><p>' + msg + '</p></div>');
		$('.kt-module-actions').first().prepend($alert);
		setTimeout(function () { $alert.fadeOut(function () { $alert.remove(); }); }, 5000);
	}

})(jQuery);

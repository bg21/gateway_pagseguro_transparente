<!DOCTYPE html>
<html>
<head>
	<title></title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

</head>
<body>
	<!----Utilizando bootstrap 4--->

	<section class="pagamento">
		<div class="container">
			<h2>Efetuar Pagamento</h2>
			<form>
				<div class="row">
					<div class="col-md-12">
					  <div class="form-group">
					    <label for="exampleInputNome">Nome Completo</label>
					    <input type="text" name="nome" class="form-control" id="exampleInputNome" aria-describedby="nomeHelp">
					  </div>						
					</div>
					<div class="col-md-12">
						<div class="form-group">
						    <label for="exampleInputCpf">CPF:</label>
						    <input type="text" name="cpf" class="form-control" id="exampleInputCPF" aria-describedby="cpfHelp">
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
						    <label for="exampleInputBandeiras">Bandeiras:</label>
						    <select class="form-control" name="bandeira">
						    	
						    </select>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
						    <label for="exampleInputValor">Valor:</label>
						    <select class="form-control" name="valores">
						    	
						    </select>
						</div>
					</div>

					<div class="col-md-6">
						<div class="form-group">
						    <label for="exampleInputCartao">Número do Cartão:</label>
						    <input type="text" name="numero_cartao" class="form-control" id="exampleInputCartao" aria-describedby="cartaoHelp">
						</div>
					</div>

					<div class="col-md-6">
						<div class="form-group">
						    <label>Mês do Cartão:</label>
						    <input type="text" name="mes_validade" class="form-control">
						</div>
					</div>

					<div class="col-md-6">
						<div class="form-group">
						    <label>Ano do Cartão:</label>
						    <input type="text" name="ano_validade" class="form-control">
						</div>
					</div>

					<div class="col-md-6">
						<div class="form-group">
						    <label for="exampleInputCVV">CVV:</label>
						    <input type="text" name="cvv" class="form-control" id="exampleInputCVV" aria-describedby="cvvHelp">
						</div>
					</div>
					<div class="col-md-12">
						<button type="submit" class="btn btn-primary">Enviar</button>
					</div>
				</div>
			</form>
		</div>
	</section>

	<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>

	<script src="https://stc.sandbox.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js"></script>

	<script type="text/javascript">
	let valor = 400.5;
	let imagens = [];

	//listar bandeiras
	$.ajax({
		dataType: 'json',
		url: 'cartao_credito.php',
		method: 'post',
		data: {'gerar_sessao':'true'}
	}).done(function(data){
		console.log(data);
		PagSeguroDirectPayment.setSessionId(data.id);
		PagSeguroDirectPayment.getPaymentMethods({
			success: function(response){
				var bancos = '';
				var bandeiras = '';
				
				$.each(response.paymentMethods.CREDIT_CARD.options, function(key, value){
					imagens[value.name.toLowerCase()] = 'https://stc.pagseguro.uol.com.br'+value.images.MEDIUM.path;
					bandeiras+='<option value="'+value.name.toLowerCase()+'">'+value.name+'</option>';
				})
				$('select[name=bandeira]').html(bandeiras);
			}
		});
	})

	//Detectando a bandeira do cartão

	$('input[name=numero_cartao]').on('keyup', function(){
		if($(this).val().length >= 6){
			PagSeguroDirectPayment.getBrand({
				cardBin: $(this).val().substring(0,6),
				success: function(v){
					var cartao = v.brand.name;
					PagSeguroDirectPayment.getInstallments({
						amount: valor,
						maxInstallmentNoInterest: 4,
						brand: cartao,
						success: function(data){
							var bandeirasSelect = $('select[name=bandeira]');
							bandeirasSelect.find('option').removeAttr('selected');
							bandeirasSelect.find('option[value='+cartao+']').attr('selected', 'selected');

							//listar opções parcelamento
							$('select[name=valores]').html('');
							$.each(data.installments[cartao], function(index, value){
								var htmlAtual = $('select[name=valores]').html();
								var valorParcela = value.installmentAmount;
								var juros = value.interestFree == true ? ' sem júros' : ' com júros';
								$('select[name=valores]').html(htmlAtual+'<option value="'+(index+1)+':'+valorParcela+'">'+valorParcela+juros+'</option>');
							});

						}
					})
				}
			})
		}
	});

	$('select[name=bandeira]').change(function(){
		var bandeira = $(this).val();
		PagSeguroDirectPayment.getInstallments({
			amount: valor,
			maxInstallmentNoInterest: 4,
			brand: bandeira,
			success: function(data){
				$('select[name=valores]').html('');
				$.each(data.installments[bandeira], function(index, value){
					var htmlAtual = $('select[name=valores]').html();
					var valorParcela = value.installmentAmount;
					var juros = value.interestFree == true ? ' sem júros' : ' com júros';
					$('select[name=valores]').html(htmlAtual+'<option value="'+(index+1)+':'+valorParcela+'">'+valorParcela+juros+'</option>');
				});

			}
		})
	})


	//Formulário principal
	$('form').submit(function(e){
		e.preventDefault();
		disabledForm();

		var numero_cartao = $('[name=numero_cartao]').val();
		var cvv = $('[name=cvv]').val();
		var bandeira = $('[name=bandeira]').val();
		var parcela = $('[name=valores]').val();
		var mes = $('[name=mes_validade]').val();
		var ano = $('[name=ano_validade]').val();

		var hash = PagSeguroDirectPayment.getSenderHash();

		//pegar bandeira
		PagSeguroDirectPayment.createCardToken({
			cardNumber: numero_cartao,
			brand: bandeira,
			cvv: cvv,
			expirationMonth: mes,
			expirationYear: ano,
			success: function(data){
				var token = data.card.token;
				var splitParcelas = parcela.split(':');
				var valorParcela = splitParcelas[1];
				var numeroParcela = splitParcelas[0];

				$.ajax({
					method: 'post',
					dataType: 'json',
					url: 'cartao_credito.php',
					data: {'fechar_pedido': true, 'token': token, 'cartao': bandeira, 'parcelas': numeroParcela, 'valorParcela': valorParcela, 'hash':hash, 'amount': valor},
					success: function(data){
						if(data.status == undefined){
							console.log('erro ao pagar');
						}else{
							enableForm();
							alert('Pagamento efetuado com sucesso!');
						}
					}
				})
			},
			error: function(data){
				console.log(data);
			}
		})
	});

	function disabledForm(){
		$('form').animate({
			'opacity':'0.4'
		})
		$('form').find('button').attr('disabled', 'disabled');
		$('form').find('input').attr('disabled', 'disabled');
		$('form').find('select').attr('disabled', 'disabled');
	}

	function enableForm(){
		$('form').animate({
			'opacity':'1'
		})
		$('form').find('button').removeAttr('disabled');
		$('form').find('input').removeAttr('disabled');
		$('form').find('select').removeAttr('disabled');
	}
	</script>
</body>
</html>
<?php
//recebe os dados do formulario.html
//depois vai validar, depois vai salvar e mostrar
// o resultado na tela par o usuario

date_default_timezone_set("America/Sao_Paulo");


// so acitaremos dados vindo do post (la do index,html)
if ($_SERVER["REQUEST_METHOD"] !== "Post") {
    header("Loation: index.html");
    exit;
}



function e($texto) {
      return htmlspecialchars($texto, ENT_QUOTES, "UTF-8");
}

//Recebendo os dados vindos do formulario 
// ?? devolve um valor padrão se o campo não existir 
// ele vai evitar erros do tipo: undefined index
$nome = trim($_POST["nome"] ?? "");
$email = trim($_POST["email"] ?? "");
$idade = trim($_POST["idade"] ?? "");

$estado = $_POST["estado"] ?? "";
$nota = $_POST["nota"] ?? "";
$origem = $_POST["origem"] ?? "";

$comentario = trim($_POST["comentario"] ?? "");

//vai verificar se o check esta marcado - não deixa prosseguir, se não estiver 
$termos = trim($_POST["termos"]);

//vamos garantir que origem, realmente seja um array
if (!is_array($origem)){
    //vamos fazer com o que ele seja um array!
    $origem = [];
}

// lista de estado permitidos no formulario 
// estes estados vem do index.html, vejas quais você informou la no value do campo estado
$estadosvalidos = ["AC", "AL", "AM", "AP", "PR", "SC", "RS"];


$origemsvalidas = [
    "indicacao" => "Indicação de amigos",
    "redes" => "Redes Sociais",
    "busca" => "Pesquisa na internet",
    "outro" => "outro"
];

//validações no servidor
$erros = [];

//validação do nome, para que tenha mais que 3 caracteres 
if (mb_strlen($nome) < 3){
    $erros[] = "O nome deve ter pelo menos três (3) caracteres.";
} elseif (!preg_match("/^[\p{L}\s']+$/u", $nome)) {
$erros[] = "Use apenas letras no nome.";
}

// validação do e-email
// aqui vamos usar a função filter_var, ela ja faz tudo, ja e um validador pronto
if (!filter_var($email, FILTER_VALIDADE_EMAIl)) {
    $erros[] = "Digite um e-email valido";
}



$idadeInt = filter_var($idade, FILTeR_VALIDATE_INT, [
    "options" => ["min_range" => 10, "max_range" => 120]
]);
if ($idadeInt === false) {
    $erros[] = "A idade deve ser um numero inteiro entre 10 e 120.";
}

//verificando os estados validos 
if (!in_array($estado, $estadosvalidos, true)){
    $erros[] = "Selecione um estado valido";
}

// Nota, tem que ser inteiro de 1 a 5 apenas 
$notaInt = filter_var($nota, FILTER_VALIDATE_INT, [
    "options" => ["min-range" => 1, "max-range" => 5]
]);
if ($notaIn === false) {
    $erros[] = "Escolha uma nota de 1 a 5";

}

//vamos validar a origem - os checkbox
if (count($origem) === 0){
    $erros[] = "Marque pelo menos uma opção em 'como você conheceu o curso'. ";
} else {
    foreach($origem as $item) {
        if (!is_string($item) || !array_key_exists($item, $origemsvalidas)){
            $erros[] = "Opção de origem invalida.";
            break;
        }

    }
}


if ($comentario !== "") {

if (mb_strlen($comentario) < 10) {
     $erros[] = "O comentario deve ter pelo menos 10 caracteres ou ficar em branco!";

} elseif (mb_strlen($comentario) > 300) {
    $erros[] = "O comentario pode ter no maximo 300 caracteres";
}

}

// vamos validar os termos, que precisa ser marcado
if (!$termos) {
    $erros[] = "Você precisa concordar com os termos para enviar.";
}

// SE TIVER ERROS, VOU MOSTAR-LOS AO USUARIO 
// SE NÃO TIVER ERROS, VAMOS SALVAR OS DADOS
if (count($erros) > 0) {
    //se entrou nesta condição, que dizer que tem erros no formulario 
$titulo = "Corrija os campos abaixo";
$tipo = "erro";
} else {
    //se não tem erro no formulario, vamos gravar os dados em um arquivo CVS
    $arquivo = __DIR__ ."/respostas.csv";
    $novoArquivo = !file_exists($arquivo);



    //abrindo arquivo e adicionar as colunas 
    $f = fopen($arquivo, "a"); //"a" significa que vou adicionar no final do arquivo

if ($f) {
    //se o arquivo ainda não existir, criamos as colunas dele
    if ($novoArquivo) {
        fputcsv($f, ["Data", "nome", "E-mail", "idade", "Estado", "nota", "origem", "comentario"]);
    }
//agora vamos inserir os dados dos campos no arquivo
fputcsv($f, [
    date("d/m/Y H:I:s"), $nome, $email, $idade,  $estado, $nota, implode(" | "$origem), $comentario

]);
fclose($f);

}
$titulo = "obrigado pela sua resposta";
$tipo = "Sucesso";

}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resulatdo da pesquisa</title>
    <link rel="stylesheet" href="style.css">
    <style>
      /* Estilo adicionais, que são so desta pagina */
      .resumo {lis-style: none; padding: 0; margin: 20px 0;}
      .resumo li{padding: 8px 0; border-bottom: 1px solid #e2e8f0;}
      .lista-erros {color: #e53e3e; padding-left: 20px;}
       .titulo-erro {color: #e53e3e; }
      .titulo-sucesso {color: #38a169; }
      .link-voltar {dispaly: block; text-align: center; text-decoration: none;}

      </style>
 </head>
<body>
    
<main class="container">
<?php if ($tipo === "erro"): ?>
<h1 class="titulo-erro"> <?= e($titulo) ?> </h1>
<!-- lista dos erros para o usuario corrigir -->
 <u1 class="lista-erros">
<!-- Recuperando os erros do nosso vetor de erro $erro -->
 <?php foreach ($erros as $erro) ?>
 <li> <?= e($erro) ?> </li>
 <?php endforeach; ?>
</U>


<!-- volta para a tela de pesquisa, mas mantem o que o usuario ja preencheu -->
 <a href="javascript:history.back()" class="botao link-voltar">
    voltar e corrigir
     </a>
     <?php else: ?>
        <h1 class="titulo-sucesso"> <?= e($titulo) ?> </h>
        <p> <?= e($nome) ?> sua resposta foi registrada com sucesso! </P>

        <ul class="resumo">
            <li> <strong>E-mail: </strong> <?= e($email) ?> </li>
            <li> <strong>Idade: </strong> <?= (int) $idadeInt ?> anos </li>
            <li> <strong>Estado: </strong> <?= e($estado) ?>  </li>
            <li> <strong>Nota para o curso: </strong> <?= (int) $notaInt ?> de 5 </li>
       
        <!-- agora amos tratar a origem -->
            <li><strong>como conheceu: </strong>
            <?php
$textos =[];
foreach ($origem as $item) {
    $textos[] = $origemValidas[$item];
}
echo e(implode(",", $textos));
?>
</li>
    
<!-- agora vamos trabalhar o comentario -->
 <!-- verificando se o usuario  digitou algo no comentario -->
  <?php if ($comentario !== ""): ?>
<li>
    <strong>Comentario:</strong><br>
    <!-- vou mostrar o comentario conforme digitação do usuario -->
<?= nl2br(e($comentario)) ?>
  </li>

 </ul>
<!-- Mostrar botão para o usuario responder o questionario novamente -->
 <a href="index.html" class="botao link-voltar">Responder Novamente</a>
 <?php endif; ?>
</main>

</body>
</html>
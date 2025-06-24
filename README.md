# 🏫 Seleção Simplificada de Professores - SME Caucaia 2025

Este repositório contém o sistema web utilizado pela **Secretaria Municipal de Educação de Caucaia (SME Caucaia)** para o processo de **Seleção Simplificada de Professores Temporários - 2025**.

## 📋 Funcionalidades

- Verificação de CPF para acesso ao formulário de inscrição.
- Preenchimento e envio online do formulário com upload de documentação.
- Geração automática de número de inscrição.
- Emissão de comprovante em PDF.
- Consulta de 2ª via do comprovante por CPF.
- Área administrativa com painel de acompanhamento e gestão.
- Filtros e visualização dos dados dos candidatos.
- Exportação de dados.
- Sistema otimizado para acesso em dispositivos móveis.

## 🚀 Tecnologias Utilizadas

- **Laravel** (PHP)
- **Bootstrap 5**
- **MySQL**
- **JavaScript (jQuery + Mask Plugin)**
- **Certbot + NGINX (proxy reverso e HTTPS)**

## 📦 Estrutura

O sistema foi projetado para ser leve, seguro e acessível, com backend Laravel rodando atrás de um proxy reverso NGINX com certificado SSL (Let's Encrypt). Cada edital pode ser implantado em uma VM separada, isolando os acessos e garantindo estabilidade.

## 🛡️ Considerações

Este projeto foi desenvolvido pela **Gerência de Tecnologia da Informação da SME Caucaia**, com o objetivo de facilitar os processos seletivos e garantir maior transparência e acessibilidade aos candidatos.

O sistema segue os princípios da Lei Geral de Proteção de Dados (LGPD), armazenando informações de forma segura e criptografada.


## Comandos Importantes:

dentro da Pasta do Projeto:
php artisan tinker
\App\Models\User::create([
    'name' => 'Nome do Usuário',
    'email' => 'E-mail do usuário',
    'password' => bcrypt('senha')
]);

---

**Desenvolvido pela Gerência de Tecnologia - SME Caucaia**  
📍 Caucaia - Ceará - Brasil  

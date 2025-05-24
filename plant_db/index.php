<?php
require_once 'db.php';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

function listPlantes($db) {
    $query = $db->query("SELECT * FROM plants");
    $plantes = $query->fetchAll(PDO::FETCH_ASSOC);
    echo "<h2>Liste des plantes</h2><a href='?action=add'>➕ Ajouter</a><ul>";
    foreach ($plantes as $plante) {
        echo "<li>
                <strong>{$plante['name']}</strong> |
                <a href='?action=detail&id={$plante['id']}'>👁️</a> |
                <a href='?action=edit&id={$plante['id']}'>✏️</a> |
                <a href='?action=delete&id={$plante['id']}'>🗑️</a>
              </li>";
    }
    echo "</ul>";
}

function showDetail($db, $id) {
    $stmt = $db->prepare("SELECT * FROM plants WHERE id = ?");
    $stmt->execute([$id]);
    $plante = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($plante) {
        echo "<h2>Détails de la plante</h2><ul>";
        foreach ($plante as $key => $value) {
            if ($key === "pet_friendly") {
                $value = $value ? "Oui" : "Non";
            }
            echo "<li><strong>$key</strong>: $value</li>";
        }
        echo "</ul><a href='?'>Retour</a>";
    } else {
        echo "Plante introuvable.";
    }
}


function showForm($plante = null) {
    $fields = [
        'name' => '',
        'species' => '',
        'sunlight' => '',
        'watering' => '',
        'pet_friendly' => 0,
        'height_cm' => 0,
    ];

    if ($plante) $fields = array_merge($fields, $plante);

    $id = $plante['id'] ?? '';
    $action = $id ? "edit&id=$id" : "add";

    echo "<h2>" . ($id ? "Modifier" : "Ajouter") . " une plante</h2>";
    echo "<form method='POST' action='?action=$action'>
        <label>Nom: <input type='text' name='name' value='{$fields['name']}' required></label><br>
        <label>Espèce: <input type='text' name='species' value='{$fields['species']}'></label><br>
        <label>Lumière: <input type='text' name='sunlight' value='{$fields['sunlight']}'></label><br>
        <label>Arrosage: <input type='text' name='watering' value='{$fields['watering']}'></label><br>
        <label>Adaptée aux animaux: 
            <input type='checkbox' name='pet_friendly' value='1' " . ($fields['pet_friendly'] ? "checked" : "") . ">
        </label><br>
        <label>Hauteur (cm): <input type='number' name='height_cm' value='{$fields['height_cm']}'></label><br>
        <input type='submit' value='Enregistrer'>
    </form><a href='?'>Annuler</a>";
}

function handleAdd($db) {
    $stmt = $db->prepare("INSERT INTO plants (name, species, sunlight, watering, pet_friendly, height_cm) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['name'],
        $_POST['species'],
        $_POST['sunlight'],
        $_POST['watering'],
        isset($_POST['pet_friendly']) ? 1 : 0,
        $_POST['height_cm']
    ]);
    header('Location: ?');
    exit;
}

function handleEdit($db, $id) {
    $stmt = $db->prepare("UPDATE plants SET name=?, species=?, sunlight=?, watering=?, pet_friendly=?, height_cm=? WHERE id=?");
    $stmt->execute([
        $_POST['name'],
        $_POST['species'],
        $_POST['sunlight'],
        $_POST['watering'],
        isset($_POST['pet_friendly']) ? 1 : 0,
        $_POST['height_cm'],
        $id
    ]);
    header("Location: ?action=detail&id=$id");
    exit;
}

function handleDelete($db, $id) {
    $stmt = $db->prepare("DELETE FROM plants WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: ?");
    exit;
}

echo "<!doctype html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Gestion des plantes</title>
    <link rel='stylesheet' href='style.css'>
</head>
<body>";
echo "
<div class='header'>
    <img src='media/plante_01.jpg' alt='Bannière Plantes'>
</div>
";

echo "<h1>Bienvenue dans le paradis des plantes</h1>";

switch ($action) {
    case 'list':
        listPlantes($db);
        break;
    case 'detail':
        showDetail($db, $id);
        break;
    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            handleAdd($db);
        } else {
            showForm();
        }
        break;
    case 'edit':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            handleEdit($db, $id);
        } else {
            $stmt = $db->prepare("SELECT * FROM plants WHERE id = ?");
            $stmt->execute([$id]);
            $plante = $stmt->fetch(PDO::FETCH_ASSOC);
            showForm($plante);
        }
        break;
    case 'delete':
        handleDelete($db, $id);
        break;
    default:
        echo "Action non reconnue.";
}

echo "</body></html>";

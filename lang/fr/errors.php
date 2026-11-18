<?php

declare(strict_types=1);

return [
    'ui' => [
        'page_title' => 'Catalogue des erreurs API (RFC 7807) — LaraApiStarter',
        'badge_rfc' => 'Problem Details RFC 7807',
        'badge_standard' => 'Erreurs standardisées',
        'heading' => 'Catalogue des erreurs API',
        'subtitle' => 'Spécification et référence développeur des codes d\'erreur applicatifs retournés par l\'API. Conforme à la norme RFC 7807 Problem Details.',
        'search_placeholder' => 'Filtrer par code, statut HTTP ou mot-clé (tapez "/" pour chercher)...',
        'all_categories' => 'Toutes les catégories',
        'all_statuses' => 'Tous les statuts',
        'showing_count' => 'Affichage de :shown sur :total codes d\'erreur',
        'copy_link' => 'Copier le lien',
        'copied' => 'Copié !',
        'copy_code' => 'Copier le code',
        'copy_payload' => 'Copier la charge JSON',
        'example_payload' => 'Exemple de charge utile RFC 7807',
        'api_docs' => 'Référence API (Scalar)',
        'github' => 'Dépôt GitHub',
        'no_results' => 'Aucun code d\'erreur ne correspond à vos critères de recherche.',
        'reset_filters' => 'Réinitialiser les filtres',
        'theme_toggle' => 'Changer de thème',
        'language' => 'Langue',
    ],
    'codes' => [
        'UNAUTHENTICATED' => [
            'title' => 'Non authentifié',
            'description' => 'Les identifiants d\'authentification (jeton Bearer ou session) sont manquants, invalides ou expirés.',
        ],
        'UNAUTHORIZED' => [
            'title' => 'Accès refusé',
            'description' => 'L\'utilisateur authentifié ne dispose pas des permissions ou du rôle requis pour exécuter cette action.',
        ],
        'USER_NOT_FOUND' => [
            'title' => 'Utilisateur introuvable',
            'description' => 'Le compte utilisateur spécifié n\'a pas été trouvé dans le système.',
        ],
        'EMAIL_NOT_VERIFIED' => [
            'title' => 'E-mail non vérifié',
            'description' => 'La vérification de l\'adresse e-mail est obligatoire pour réaliser cette opération.',
        ],
        'EMAIL_ALREADY_VERIFIED' => [
            'title' => 'E-mail déjà vérifié',
            'description' => 'L\'adresse e-mail de l\'utilisateur a déjà été validée précédemment.',
        ],
        'VALIDATION_FAILED' => [
            'title' => 'Échec de validation',
            'description' => 'Un ou plusieurs paramètres de la requête ne respectent pas les règles de validation requises.',
        ],
        'INVALID_CREDENTIALS' => [
            'title' => 'Identifiants invalides',
            'description' => 'L\'adresse e-mail ou le mot de passe fourni ne correspond à aucun compte existant.',
        ],
        'INVALID_VALIDATION_LINK' => [
            'title' => 'Lien de validation invalide',
            'description' => 'Le lien de vérification d\'e-mail est invalide, altéré ou a expiré.',
        ],
        'PASSWORD_MISMATCH' => [
            'title' => 'Mot de passe incorrect',
            'description' => 'Le mot de passe actuel fourni ne correspond pas au mot de passe de votre compte.',
        ],
        'RATE_LIMIT_EXCEEDED' => [
            'title' => 'Limite de requêtes atteinte',
            'description' => 'Trop de requêtes ont été envoyées dans un intervalle restreint. Veuillez patienter avant de réessayer.',
        ],
        'ACTION_NOT_ALLOWED' => [
            'title' => 'Action non autorisée',
            'description' => 'Cette opération est interdite dans l\'état actuel de la ressource ou l\'environnement en cours.',
        ],
        'ENUM_NOT_FOUND' => [
            'title' => 'Énumération introuvable',
            'description' => 'Le type d\'énumération ou l\'identifiant demandé n\'existe pas dans le système.',
        ],
        'NEED_TO_BE_ADMIN' => [
            'title' => 'Rôle administrateur requis',
            'description' => 'Cette action requiert le rôle d\'administrateur ou des privilèges élevés.',
        ],
        'ACCOUNT_DISABLED' => [
            'title' => 'Compte désactivé',
            'description' => 'Ce compte utilisateur a été désactivé ou suspendu par un administrateur.',
        ],
        'RESET_PASSWORD_FAILED' => [
            'title' => 'Échec de réinitialisation',
            'description' => 'Impossible de terminer la réinitialisation du mot de passe. Le jeton peut être invalide ou expiré.',
        ],
        'NOT_FOUND' => [
            'title' => 'Route introuvable',
            'description' => 'L\'URL ou le point de terminaison demandé n\'existe pas sur ce serveur.',
        ],
        'SERVER_ERROR' => [
            'title' => 'Erreur interne du serveur',
            'description' => 'Une erreur interne imprévue est survenue lors de l\'exécution de la requête.',
        ],
        'FORBIDDEN' => [
            'title' => 'Interdit',
            'description' => 'L\'accès à la ressource ciblée est strictement interdit.',
        ],
        'METHOD_NOT_ALLOWED' => [
            'title' => 'Méthode HTTP non autorisée',
            'description' => 'La méthode HTTP utilisée n\'est pas supportée par cet endpoint.',
        ],
        'RESOURCE_NOT_FOUND' => [
            'title' => 'Ressource introuvable',
            'description' => 'L\'entité ou la ressource ciblée n\'existe pas.',
        ],
        'CONFLICT' => [
            'title' => 'Conflit de ressource',
            'description' => 'La requête entre en conflit avec l\'état actuel de la ressource ou son unicité.',
        ],
        'TOO_MANY_REQUESTS' => [
            'title' => 'Trop de requêtes',
            'description' => 'Le quota de requêtes alloué a été dépassé pour ce client ou cette adresse IP.',
        ],
        'GONE' => [
            'title' => 'Ressource disparue',
            'description' => 'La ressource a été définitivement supprimée et ne sera plus restaurée.',
        ],
        'UNPROCESSABLE' => [
            'title' => 'Entité non traitable',
            'description' => 'Le serveur comprend le format de la charge utile mais ne peut pas exécuter les instructions reçues.',
        ],
        'SERVICE_UNAVAILABLE' => [
            'title' => 'Service indisponible',
            'description' => 'Le serveur est temporairement inaccessible ou en cours de maintenance.',
        ],
        'TWO_FACTOR_ALREADY_ENABLED' => [
            'title' => '2FA déjà activée',
            'description' => 'L\'authentification à deux facteurs est déjà active sur ce compte.',
        ],
        'TWO_FACTOR_NOT_ENABLED' => [
            'title' => '2FA non configurée',
            'description' => 'L\'authentification à deux facteurs n\'est pas activée sur ce compte.',
        ],
        'TWO_FACTOR_INVALID_CODE' => [
            'title' => 'Code 2FA incorrect',
            'description' => 'Le code à usage unique (TOTP) fourni est invalide ou a expiré.',
        ],
        'TWO_FACTOR_NOT_CONFIRMED' => [
            'title' => '2FA non confirmée',
            'description' => 'L\'inscription 2FA a été initiée mais n\'a pas encore été confirmée avec un code valide.',
        ],
        'TWO_FACTOR_INVALID_RECOVERY_CODE' => [
            'title' => 'Code de secours invalide',
            'description' => 'Le code de récupération de secours fourni est invalide ou a déjà été consommé.',
        ],
        'TWO_FACTOR_REQUIRED' => [
            'title' => 'Vérification 2FA requise',
            'description' => 'Un défi d\'authentification à deux facteurs doit être complété pour continuer.',
        ],
        'API_KEY_INVALID' => [
            'title' => 'Clé API invalide',
            'description' => 'La clé d\'API fournie est inexistante, corrompue ou malformée.',
        ],
        'API_KEY_EXPIRED' => [
            'title' => 'Clé API expirée',
            'description' => 'La clé d\'API a dépassé sa date d\'expiration et ne peut plus être utilisée.',
        ],
        'API_KEY_REVOKED' => [
            'title' => 'Clé API révoquée',
            'description' => 'La clé d\'API a été révoquée par un administrateur ou son propriétaire.',
        ],
        'IMPERSONATION_NOT_ALLOWED' => [
            'title' => 'Usurpation non autorisée',
            'description' => 'Vous ne possédez pas les autorisations nécessaires pour usurper ce compte.',
        ],
        'IMPERSONATION_SELF' => [
            'title' => 'Auto-usurpation impossible',
            'description' => 'Un administrateur ne peut pas démarrer une session d\'usurpation sur son propre compte.',
        ],
        'SUSPICIOUS_REQUEST' => [
            'title' => 'Requête suspecte bloquée',
            'description' => 'La requête a été rejetée par les filtres de sécurité (suspicion d\'injection SQL, XSS ou path traversal).',
        ],
        'REQUEST_TOO_LARGE' => [
            'title' => 'Charge utile trop volumineuse',
            'description' => 'Le volume de la requête HTTP dépasse le seuil maximal configuré pour cette instance.',
        ],
        'WEBHOOK_NOT_FOUND' => [
            'title' => 'Webhook introuvable',
            'description' => 'La souscription au webhook spécifiée n\'a pas été trouvée.',
        ],
        'WEBHOOK_DELIVERY_FAILED' => [
            'title' => 'Échec de livraison webhook',
            'description' => 'La charge utile du webhook n\'a pas pu être délivrée à l\'URL configurée.',
        ],
        'MEDIA_NOT_FOUND' => [
            'title' => 'Média introuvable',
            'description' => 'Le fichier multimédia demandé est introuvable dans le système de stockage.',
        ],
        'MEDIA_UPLOAD_FAILED' => [
            'title' => 'Échec de téléversement média',
            'description' => 'Le fichier n\'a pas pu être téléversé ou traité correctement.',
        ],
        'EXPORT_NOT_FOUND' => [
            'title' => 'Export introuvable',
            'description' => 'La tâche d\'export de données ou le fichier généré est introuvable ou a expiré.',
        ],
        'EXPORT_FAILED' => [
            'title' => 'Échec de l\'export',
            'description' => 'La génération asynchrone de l\'export de données a échoué.',
        ],
    ],
];

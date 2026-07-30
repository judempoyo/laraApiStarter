<?php

declare(strict_types=1);

return [
    // ── Auth — Inscription ────────────────────────────────────────────────
    'register_success'           => 'Inscription réussie. Veuillez vérifier votre adresse e-mail.',

    // ── Auth — Connexion ──────────────────────────────────────────────────
    'login_success'              => 'Connexion réussie.',
    'account_disabled'           => 'Votre compte a été désactivé par un administrateur.',

    // ── Auth — Déconnexion ────────────────────────────────────────────────
    'logout_success'             => 'Déconnexion réussie.',
    'logout_all_success'         => 'Déconnexion de tous les appareils réussie.',
    'session_revoked'            => 'Session révoquée avec succès.',
    'other_sessions_revoked'     => 'Toutes les autres sessions ont été révoquées.',

    // ── Auth — Token ──────────────────────────────────────────────────────
    'token_refreshed'            => 'Token actualisé avec succès.',

    // ── Auth — Vérification d'e-mail ─────────────────────────────────────
    'email_verified'             => 'Adresse e-mail vérifiée avec succès.',
    'email_already_verified'     => 'Adresse e-mail déjà vérifiée.',
    'email_already_verified_err' => 'Votre adresse e-mail est déjà vérifiée.',
    'verification_link_sent'     => 'Un nouveau lien de vérification a été envoyé.',
    'invalid_verification_link'  => 'Lien de vérification invalide.',

    // ── Auth — Vérification d'e-mail ─────────────────────────────────────
    'email_exists'               => 'Adresse e-mail existante.',
    'email_not_found'            => 'Adresse e-mail introuvable.',

    // ── Auth — Profil ─────────────────────────────────────────────────────
    'profile_updated'            => 'Profil mis à jour avec succès.',
    'email_changed'              => 'E-mail modifié avec succès. Un nouveau lien de vérification a été envoyé.',
    'password_updated'           => 'Mot de passe mis à jour avec succès.',
    'wrong_current_password'     => 'Le mot de passe fourni ne correspond pas à votre mot de passe actuel.',
    'avatar_updated'             => 'Photo de profil mise à jour.',
    'avatar_deleted'             => 'Photo de profil supprimée.',

    // ── Auth — Sessions ───────────────────────────────────────────────────
    'sessions_retrieved'         => 'Sessions récupérées avec succès.',

    // ── Authentification à deux facteurs ──────────────────────────────────
    '2fa_setup'                  => 'Scannez le QR code avec votre application d\'authentification, puis confirmez avec le code généré.',
    '2fa_already_enabled'        => 'L\'authentification à deux facteurs est déjà activée.',
    '2fa_already_enabled_msg'    => 'L\'authentification à deux facteurs est déjà active sur ce compte.',
    '2fa_confirmed'              => 'L\'authentification à deux facteurs a été activée.',
    '2fa_verified'               => 'Code à deux facteurs vérifié avec succès.',
    '2fa_disabled'               => 'L\'authentification à deux facteurs a été désactivée.',
    '2fa_not_enabled'            => 'L\'authentification à deux facteurs n\'est pas activée.',
    '2fa_not_confirmed'          => 'L\'authentification à deux facteurs n\'a pas encore été confirmée.',
    '2fa_invalid_code'           => 'Le code fourni est invalide ou a expiré.',
    '2fa_invalid_password'       => 'Le mot de passe fourni est incorrect.',

    // ── Clés API ──────────────────────────────────────────────────────────
    'api_keys_retrieved'         => 'Clés API récupérées avec succès.',
    'api_key_created'            => 'Clé API créée. Sauvegardez-la maintenant — elle ne sera plus affichée.',
    'api_key_revoked'            => 'Clé API révoquée avec succès.',
    'api_key_not_found'          => 'Clé API introuvable.',

    // ── Impersonation ─────────────────────────────────────────────────────
    'impersonation_started'      => 'Impersonation démarrée.',
    'impersonation_stopped'      => 'Impersonation arrêtée.',
    'impersonation_self_error'   => 'Vous ne pouvez pas vous impersonifier vous-même.',

    // ── Notifications ─────────────────────────────────────────────────────
    'notifications_retrieved'    => 'Notifications récupérées avec succès.',
    'notification_read'          => 'Notification marquée comme lue.',
    'notifications_all_read'     => 'Toutes les notifications ont été marquées comme lues.',
    'notification_deleted'       => 'Notification supprimée.',

    // ── Préférences ───────────────────────────────────────────────────────
    'preferences_retrieved'      => 'Préférences récupérées avec succès.',
    'preference_set'             => 'Préférence enregistrée.',
    'preference_deleted'         => 'Préférence supprimée.',

    // ── Bilan de santé ────────────────────────────────────────────────────
    'health_ok'                  => 'Tous les systèmes sont opérationnels.',
    'health_degraded'            => 'Un ou plusieurs services sont dégradés.',

    // ── Auth (générique) ──────────────────────────────────────────────────
    'invalid_credentials'        => 'Identifiants invalides.',
    'invalid_credentials_message' => 'Les identifiants fournis sont incorrects.',
    'unauthenticated'            => 'Non authentifié.',
    'unauthenticated_message'    => 'Vous n\'êtes pas authentifié.',
    'unauthorized'               => 'Accès refusé.',
    'unauthorized_message'       => 'Vous n\'avez pas la permission d\'effectuer cette action.',
    'validation_failed'          => 'La validation a échoué.',
    'not_found'                  => ':resource introuvable.',
    'not_found_message'          => 'La ressource :resource demandée n\'existe pas.',
    'conflict'                   => ':resource existe déjà.',
    'gone'                       => ':resource a été supprimé définitivement.',
    'gone_message'               => 'La ressource :resource demandée n\'existe plus.',
    'too_many_requests'          => 'Trop de requêtes.',
    'too_many_requests_message'  => 'Vous avez dépassé votre limite de requêtes. Veuillez réessayer plus tard.',
    'server_error'               => 'Une erreur inattendue s\'est produite.',
    'server_error_message'       => 'Une erreur inattendue s\'est produite. Veuillez réessayer plus tard.',
    'service_unavailable'        => 'Service temporairement indisponible.',
    'service_unavailable_message' => 'Le service est temporairement indisponible. Veuillez réessayer plus tard.',

    // ── Webhooks ──────────────────────────────────────────────────────────
    'webhook_created'            => 'Webhook enregistré avec succès.',
    'webhook_updated'            => 'Webhook mis à jour avec succès.',
    'webhook_deleted'            => 'Webhook supprimé avec succès.',
    'webhooks_retrieved'         => 'Webhooks récupérés avec succès.',
    'webhook_deliveries'         => 'Historique des envois de webhook récupéré.',
    'webhook_redelivered'        => 'Renvoi du webhook mis en file d\'attente.',
    'webhook_not_found'          => 'Webhook introuvable.',
    'webhook_delivery_not_found' => 'Envoi de webhook introuvable.',
    'available_events'           => 'Événements webhook disponibles récupérés.',

    // ── Médias ────────────────────────────────────────────────────────────
    'media_uploaded'             => 'Fichier téléversé avec succès.',
    'media_deleted'              => 'Fichier supprimé avec succès.',
    'media_retrieved'            => 'Fichiers médias récupérés avec succès.',
    'media_url_generated'        => 'URL temporaire générée.',
    'media_not_found'            => 'Fichier introuvable.',
    'media_upload_failed'        => 'Le téléversement du fichier a échoué.',

    // ── Exports ───────────────────────────────────────────────────────────
    'export_queued'              => 'Export mis en file d\'attente. Vous serez notifié lorsqu\'il sera prêt.',
    'export_retrieved'           => 'Exports récupérés avec succès.',
    'export_not_found'           => 'Export introuvable.',
    'export_not_ready'           => 'L\'export n\'est pas encore prêt au téléchargement.',
    'export_ready'               => 'Votre export est prêt au téléchargement.',
    'export_resources_listed'    => 'Ressources exportables disponibles récupérées.',
];

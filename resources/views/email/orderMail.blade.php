<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta name="robots" content="noindex,nofollow" />
        <style>
            body { width: 800px; background-color:rgb(255, 255, 255); color:rgb(0, 0, 0); font: 14px/1.4 Helvetica, Arial, sans-serif; margin: 0; padding-bottom: 45px; }
            table { width: 800px; border: 0px rgb(255, 255, 255); margin: 1em 0; border-width: 0px 0;}
            table th, table td { border: 0px rgb(255, 255, 255); border-width: 0px 0;  border-collapse: collapse;}
            table th { padding: 0; margin: 0; font-size: 14px !important;}
            table td { padding: 0; margin: 0; color:rgb(0, 0, 0); font-size: 13px !important; }
            .container { width: 800px; ;margin: 0 auto; padding: 0 15px; text-align: center; }
            .header { width: 800px; background: rgb(255, 255, 255); padding: 20px; margin-bottom: 20px; }
            .order-text { font-weight: bold; font-size: 17px; margin-bottom: 20px; margin-top: 20px; }
            .order-info { margin-bottom: 20px; text-align: left; }
            .footer { font-size: 12px; margin: auto; width: 800px; padding: 10px; text-align: left; color:rgb(0, 0, 0); }
            .total { text-align: right; padding: 10px; padding-top: 10px; font-size: 14px; font-weight: bold; }
            div {padding: 0; margin: 0;}
            p {padding: 0; margin: 0; font-size: 12px !important;}
            .footer-bold { font-weight: bold; font-size: 13px; text-align: center;}
            .footer-bold-left { font-weight: bold; font-size: 13px; text-align: left;}
            .footer-bold-left_sub { margin-top: 15px; font-weight: bold; font-size: 13px; text-align: left; text-decoration: underline;}
            .font_sub { text-decoration: underline;}
            .footer-normal { font-size: 10px;}

        </style>
    </head>
    <body>
        <div class="container">

            <div class="header">
                <table>
                    <tr>
                        <th width="50%" align="left"><img src="{{ $message->embed($imagePath) }}" alt="Logo" width="125"></th>
                        <td width="50%" align="right">
                            <div>{{ $pharmacy->pharmacy_cip13 }}</div>
                            <div>{{ $pharmacy->pharmacy_name }}</div>
                            <div>{{ $pharmacy->pharmacy_address_street }}</div>
                            <div>{{ $pharmacy->pharmacy_zipcode }} {{ $pharmacy->pharmacy_city }}</div>
                        </td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td align="center">
                            <div class="order-text">
                                CONFIRMATION DE COMMANDE
                            </div>
                        </td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td align="right">
                            <div class="order-info">
                                <table>
                                    <tr>
                                        <td>N° commande: {{ $order->order_reference ? $order->order_reference : $order->customer_po }}</td>
                                    </tr>
                                    <tr>
                                        <td>Date: {{ $order->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td align="right">
                            <div class="order-info">
                                <table>
                                    <tr>
                                        <td>Bonjour Madame, Monsieur</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 2px 0; font-family: Arial, sans-serif; font-size: 11px; color: #000000;">
                                            Nous vous remercions pour votre commande reçue le {{ \Carbon\Carbon::parse($order->created_at)->locale('fr_FR')->isoFormat('DD MMMM YYYY') }} à {{ $order->created_at->format('H\hi') }}. Celle-ci sera expédiée et facturée selon le tarif en vigueur.
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
                                <table>
                    <tr>
                        <td align="right">
                            <div class="order-info"
                                <table>
                                    <tr>
                                        <td>Pour rappel, vous avez commandé au total {{ $order->getTotalProducts() }} produit(s).</td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td align="center">
                            @if($order->items && count($order->items) > 0)
                                <div>
                                    <table style="border: none !important; border-collapse: collapse;">
                                        <thead style="background-color:rgb(193, 191, 191) !important; border: none !important;">
                                            <tr style="background-color:rgb(193, 191, 191) !important; border: none !important;">
                                                <th style="background-color:rgb(193, 191, 191) !important; border: none !important;">Code</th>
                                                    <th style="background-color:rgb(193, 191, 191) !important; border: none !important;">Désignation</th>
                                                <th style="background-color:rgb(193, 191, 191) !important; border: none !important;">Quantité</th>
                                                <th style="background-color:rgb(193, 191, 191) !important; border: none !important;">Prix unitaire remisé</th>
                                                <th style="background-color:rgb(193, 191, 191) !important; border: none !important;">Total HT</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($order->items as $item)
                                                <tr>
                                                    <td>{{ $item->product->product_cip13 ?? 'N/A' }}</td>
                                                    <td style="font-size: 11px !important;">{{ $item->product->product_presentation ?? 'N/A' }}</td>
                                                    <td align="center">{{ $item->order_detail_quantity }}</td>
                                                    <td align="center">{{ number_format($item->order_detail_price_with_dto ?? 0, 2) }}</td>
                                                    <td align="center">{{ number_format(($item->order_detail_quantity * ($item->order_detail_price_with_dto ?? 0)), 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <div class="total" style="margin-top: 10px;">
                                        Montant total: {{ number_format($order->getTotal(), 2) }}
                                    </div>
                                </div>
                            @endif
                            @if($order->lines && count($order->lines) > 0)
                                <div>
                                    <table style="border: none !important; border-collapse: collapse;">
                                        <thead style="background-color:rgb(193, 191, 191) !important; border: none !important;">
                                            <tr style="background-color:rgb(193, 191, 191) !important; border: none !important;">
                                                <th style="background-color:rgb(193, 191, 191) !important; border: none !important;">Code</th>
                                                <th style="background-color:rgb(193, 191, 191) !important; border: none !important;">Désignation</th>
                                                <th style="background-color:rgb(193, 191, 191) !important; border: none !important;">Quantité</th>
                                                <th style="background-color:rgb(193, 191, 191) !important; border: none !important;">Prix unitaire remisé</th>
                                                <th style="background-color:rgb(193, 191, 191) !important; border: none !important;">Total HT</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($order->lines as $item)
                                                <tr>
                                                    <td>{{ $item->product->product_cip13 ?? 'N/A' }}</td>
                                                    <td style="font-size: 11px !important;">{{ $item->product->product_presentation ?? 'N/A' }}</td>
                                                    <td align="center">{{ $item->qty }}</td>
                                                    <td align="center">{{ $item->discount_value > 0 ?
                                                                            number_format( $item->product->product_unit_price_pght - ( $item->product->product_unit_price_pght * $item->discount_value / 100  ), 2)
                                                                        :
                                                                            number_format($item->product->product_unit_price_pght, 2 )
                                                                        }}</td>
                                                    <td align="center">{{ $item->discount_value > 0 ?
                                                                            number_format($item->qty * ( $item->product->product_unit_price_pght - ( $item->product->product_unit_price_pght * $item->discount_value / 100 ) ), 2)
                                                                            : number_format($item->qty * $item->product->product_unit_price_pght, 2 )
                                                                        }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <div class="total" style="margin-top: 10px;">
                                        <p>Montant total: {{ number_format($order->getTotal(), 2) }}</p>
                                    </div>
                                </div>
                            @endif
                        </td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td align="center">
                            <div class="footer">
                                <p style="font-weight: bold !important;">Conditions commerciales</p>
                                <p>- Mode de paiement: LCR</p>
                                <p>- Délai de paiement: 60 jours date à date</p>
                                <p>- Délai de livraison indicatif: 3 jours ouvrés</p>
                            </div>
                        </td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td align="center">
                            <div class="footer" style="margin-top: 20px;">
                                <p>Vous disposez d’un délai de 2 heures à compter de la réception de ce message pour annuler votre commande en cliquant

                                    <?php

                                        $timestamp = time();

                                        $order_ref = $order->order_reference ? $order->order_reference : $order->customer_po;

                                    ?>
                                    <a href="https://order-noName.gmg-services.com/order_cancellation?id={{ $pharmacy->id }}&num_com={{ $order_ref }}&timestamp={{ $timestamp }}">
                                        &nbsp;ici
                                    </a>
                                </p>
                                <p>Passé ce délai, la commande sera considérée comme ferme et définitive.</p>
                            </div>
                        </td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td align="center">
                            <div class="footer" style="margin-top: 20px;">
                                <p>Votre avis nous est précieux : vous pouvez répondre à notre enquête de satisfaction en suivant ce lien: https://forms.office.com/r/SRGcUGep7D
                                </p>
                            </div>
                        </td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td align="center">
                            <div class="footer" style="margin-top: 20px;">
                                <p>En vous remerciant pour votre confiance, nous vous prions d’agréer, Madame, Monsieur, l’expression de nos salutations distinguées.
                                </p>
                            </div>
                        </td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td align="center">
                            <div class="footer" style="margin-top: 20px; margin-bottom: 10px;">
                                <p>Votre chargé(e) de clientèle CMC pour NoName Ventes Directes,
                                </p>
                            </div>
                        </td>
                    </tr>
                </table>
                <hr style="margin-bottom: 0px !important;">
                <table>
                    <tr>
                        <td align="center">
                            <div class="footer" style="margin-bottom: 10px; margin-top: 0px !important;">
                                <p>Pour tout report d'évènement indésirable, merci de contacter le département Pharmacovigilance de NoName soit par téléphone au +33 (0)1 58 07 33 89 ou +33 (0)8 00 39 84 50, soit par télécopie au +33(0)1 72 26 57 70 ou soit par e-mail à l'adresse FRA.AEReporting@noName.com<br>
Pour toute demande d'information médicale, vous pouvez vous connecter au site internet www.noName.fr (rubrique « Nous contacter » en haut) ou par téléphone au +33 01 58 07 34 40<br>
Pour suivre votre commande ou pour toute réclamation, vous pouvez nous contacter au 0800 00 44 44
                                </p>
                            </div>
                        </td>
                    </tr>
                </table>
                <hr style="margin-bottom: 0px !important;">
                <table>
                    <tr>
                        <td align="center">
                            <div class="footer" style="text-align: center !important; margin-top: 0px !important;">
                                <p>NoName, Société par Actions Simplifiée au capital de 47 570 euros, dont le siège social est situé 23-25 avenue du Docteur Lannelongue, 75668 PARIS Cedex 14, immatriculée au Registre du Commerce et des Sociétés de PARIS sous le n° B 433 623 550, locataire-gérant de NoName Holding France.</p>
                                <p>VD-CMC-2025-003 (Juin 2025)</p>
                            </div>
                        </td>
                    </tr>
                </table>
                <hr style="margin-bottom: 0px !important;">
                <table>
                    <tr>
                        <td width="50%" style="vertical-align: top !important;">
                            <div class="footer" style="width: 370px; margin-top: 10px !important; margin-right: 10px !important;">
                                <p class="footer-bold" style="margin-bottom: 15px;">
Conditions Générales de Vente applicables à compter du 1er décembre 2023<br>
Pharmacies d’Officine - France et Monaco<br>
NoName
                                </p>
                                <p class="footer-normal">
Les présentes conditions générales de vente régissent l’ensemble des commandes passées par les pharmacies d’officine et plus généralement, l’ensemble des opérations d’achat-vente des produits NoName, SAS au capital de 47 570 Euros - RCS Paris 433 623 550 - TVA FR 73 433 623 550 - 23-25 avenue du Dr. Lannelongue  F-75014 Paris, locataire-gérant de NoName Holding France, (ci-après « le vendeur ») aux pharmacies d’officine, telles que définies à l’article L.5125-1 du Code de la Santé Publique (ci-après « l’acheteur ») installées en France et Monaco.<br>
Pour connaître la liste exhaustive des produits vendus par cette société, veuillez consulter le site certifié<br>
https://medicaments.noName.fr/medicaments/index.aspx.<br>
Les présentes conditions générales de vente régissent l’ensemble des commandes passées par les pharmacies d’officine auprès de NoName locataire-gérant de NoName Holding France agissant en son nom et pour son compte.<br>
Elles remplacent les conditions générales de vente précédemment en vigueur.
                                </p>
                                <p class="footer-bold-left">
Elles régissent notamment la clause de réserve de propriété, conformément aux dispositions du Code de commerce.
                                </p>
                                <p class="footer-bold-left_sub">
ARTICLE 1 – COMMANDES
                                </p>
                                <p class="footer-normal">
Le tarif et les conditions générales de vente sont systématiquement adressés ou remis à chaque acheteur ayant fait la demande afin de lui permettre de passer commande. Ces informations sont également disponibles sur simple demande au numéro vert : 0800 004 444.<br>
Le fait de passer commande implique l’adhésion entière et sans réserve de l’acheteur aux présentes conditions générales de vente qui prévalent sur tout autre document de quelque nature que ce soit, et notamment sur les conditions générales d’achat de l’acheteur, sauf accord dérogatoire exprès et préalable du vendeur, ainsi que, en cas de contradiction, sur les conditions générales. Toute commande sera réputée confirmée lorsque le vendeur aura accusé réception de la commande par voie électronique.<br>
Le vendeur se réserve le droit de refuser, réduire ou fractionner toute commande présentant un caractère anormal sur le plan des quantités et/ou ne correspondant pas aux minima et maxima de volume par produits et/ou de prix total indiqués sur le tarif, ou pour limiter l’encours, ou lorsqu’elle émane d’un acheteur débiteur d’une facture impayée. Les quantités commandées engagent l’acheteur.<br>
Aucune modification ou annulation de commande ne sera prise en compte sauf accord exprès et écrit du vendeur. En outre, toute modification d’une commande à la demande de l’acheteur, après a cce pt at i on par le vendeur, peut entrainer une prolongation du délai de livraison.<br>
Le vendeur se réserve le droit d’apporter à tout moment les modifications qu’il juge utiles à ses produits ou découlant de la réglementation qui leur est applicable sans que celles-ci puissent justifier une modification ou annulation des commandes.<br>
Le fait pour le vendeur de ne pas se prévaloir à un moment donné de l’une quelconque des présentes conditions générales de vente ne saurait être interprété par l’acheteur comme valant renonciation à se prévaloir ultérieurement de l’une quelconque desdites conditions.<br>
                                </p>
                                <p class="footer-bold-left_sub">
ARTICLE 2 – CONDITIONS DE LIVRAISON - REPRISE DES PRODUITS
                                </p>
                                <p class="footer-normal">
<span class="font_sub">Livraisons : </span> livraisons ne seront faites qu’en France et Monaco, et sous réserve de leur disponibilité. Le vendeur est autorisé à effectuer des livraisons globales ou partielles.<br>
<span class="font_sub">Délais : </span> stipulation contraire, la livraison est réputée effectuée à la date prévue sur le bon d’acceptation de la commande. Si cette livraison est retardée, le vendeur s’engage à effectuer cette livraison dans les plus brefs délais. Toutefois, la non-observation du délai de livraison ne donne en aucun cas droit à l’annulation de la commande ou à une quelconque indemnité. En outre, toute modification d’une commande à la demande de l’acheteur, après conclusion de la vente principale, peut entrainer une prolongation du délai de livraison.<br>
A titre indicatif, il est précisé que le délai de livraison est de 3 jours ouvrés à compter de l’enregistrement de la commande.<br>
Les délais de livraison ne sont donnés qu’à titre informatif et indicatif ; ceux-ci dépendant notamment des disponibilités et de l’ordre d’arrivée des commandes. Les délais indiqués sont en outre suspendus de plein droit par l’intervention de tout évènement indépendant du contrôle du vendeur et ayant pour conséquence de retarder la livraison. Les dépassements de délais de livraison ne peuvent donner lieu ni à dommages intérêts, ni à retenue, ni à annulation des commandes en cours.<br>
<span class="font_sub">Transport et Risques : </span> produits sont vendus « site de livraison déchargés ». Le transport et le déchargement des produits s’effectuent aux risques et périls du vendeur. L’acheteur doit se rendre disponible au moment de l’arrivée du transporteur.<br>
<span class="font_sub">Transfert de Propriété : </span> livraison n’emporte pas transfert de propriété.<br>
<span class="font_sub">Réserves à la livraison : </span> réception des produits résulte de la constatation de leur conformité avec les spécifications précisées à la commande.<br>
Il appartient à l’acheteur de vérifier l’état des marchandises au moment de la livraison. En cas de produits manquants ou d’avaries, une réclamation ne sera admise et considérée comme recevable qu’après : <br>
a) Prise de réserves précises et motivées sur le(s) bordereau(x) de livraison du transporteur.<br>
b) Confirmation de ces réserves au transporteur par lettre recommandée, avec accusé de réception.<br>
dans les trois jours qui suivent la livraison, conformément à l’article L 133-3 du Code de Commerce.<br>
c) Confirmation par écrit de ces réserves au siège du vendeur dans les 8 jours suivants la livraison, à l’attention de NoName Service Clients, 23/25 avenue du Dr Lannelongue – 75668 Paris Cedex 14 ou par téléphone au 01.70.95.05.02.<br>
Tout produit n’ayant pas fait l’objet de réserves comme précisé ci-dessus, sera considéré comme accepté par l’acheteur.<br>
<span class="font_sub">Reprise de produits : </span> retour de produit doit faire l’objet d’un accord écrit entre le vendeur et l’acheteur. Tout produit retourné sans l’accord préalable et écrit du vendeur sera tenu à la disposition de l’acheteur et ne donnera pas lieu à l’établissement d’un avoir. Les frais de transport et les risques de retour demeurent en toutes circonstances, à la charge de l’acheteur. Lorsqu’après contrôle, un vice apparent ou un manquant est effectivement accepté et/ou constaté par le vendeur ou son mandataire, l’acheteur ne pourra demander que le remplacement des articles non-conformes et/ou le complément à apporter pour combler les manquants, sans que l’acheteur puisse prétendre à une quelconque indemnité ou résolution de la commande.<br>
                                </p>
                                <p class="footer-bold-left_sub">
ARTICLE 3 - PRIX – FACTURATION – DELAIS DE PAIEMENT
                                </p>
                                <p class="footer-normal">
<span class="font_sub">3.1. Prix</span> Les prix des produits sont fixés par les tarifs en vigueur au jour de la commande. Ils s’entendent nets et hors taxes. Les prix sont révisables à tout moment sur décision du vendeur dans le respect de la réglementation en vigueur.<br>
<span class="font_sub">3.2. Facturation - Délais de paiement</span> Les factures seront établies par NoName en son nom et pour son compte A chaque livraison correspond une facture et une seule. La date de remise de l’expédition au transporteur est à la fois la date d’émission de la facture et le point de départ du délai d’exigibilité du règlement. Les factures sont payables à 60 jours date de facture par LCR (Lettre de Change Relevée) sans escompte quelles que soient les modalités de paiement, y compris en cas de paiement comptant.<br>
Toutes les commandes que le vendeur accepte d’exécuter le sont, compte tenu du fait que l’acheteur présente les garanties financières suffisantes, et qu’il réglera effectivement les sommes dues à leur échéance, conformément à la législation. Aussi, si le vendeur a des raisons sérieuses ou particulières de craindre des difficultés de paiement de la part de l’acheteur à la date de la commande, ou postérieurement à celle-ci, ou encore si l’acheteur ne présente pas les mêmes garanties qu’à la date d’acceptation de la commande, le vendeur peut subordonner l’acceptation de la commande ou la poursuite de son exécution à un paiement anticipé ou en contre-remboursement et/ou à la fourniture, par l’acheteur, de garanties au profit du vendeur.<br>
Le vendeur aura également la faculté, avant l’acceptation de toute commande, comme en cours d’exécution, d’exiger de l’acheteur communication de ses documents comptables, et notamment des comptes de résultat, même prévisionnels, lui permettant d’apprécier sa solvabilité.<br>
En cas de refus par l’acheteur du paiement anticipé ou en contre-remboursement, sans qu’aucune garantie suffisante ne soit proposée par ce dernier, le vendeur pourra refuser d’honorer la(les) commande(s) passée(s) et de livrer la marchandise concernée, sans que l’acheteur puisse opposer d’un refus de vente injustifié, ou prétendre à une quelconque indemnité.<br>
<span class="font_sub">3.3. Non-paiement - Pénalités</span> L’attribution des remises ou autres avantages est subordonnée au complet règlement de la totalité des factures.<br>
<span class="font_sub">Pénalités de retard : </span> facture impayée (même partiellement) à l’échéance donnera lieu au paiement par l’acheteur de pénalités fixées à trois fois le taux d’intérêt légal. Les pénalités de retard courent de plein droit dès le jour suivant la date de règlement porté sur la facture en application de l’article L 441-10 du Code du commerce. Les pénalités sont exigibles de plein droit, dès réception de l’avis informant l’acheteur que le vendeur les a portées à son débit.<br> </p>
                            </div>
                        </td>
                        <td width="50%" style="vertical-align: top !important;">
                            <div class="footer" style="width: 370px; margin-top: 10px !important; margin-left: 10px !important;">
                                <p class="footer-normal">

<span class="font_sub">Indemnité pour frais de recouvrement : </span> acheteur en situation de retard de paiement est de plein droit débiteur d’une indemnité forfaitaire de frais de recouvrement de 40 euros. Lorsque les frais de recouvrement exposés sont supérieurs au montant de cette indemnité forfaitaire, le vendeur sera en droit de demander une indemnisation complémentaire conformément à l’article L 441-10 du Code du commerce.<br>
<span class="font_sub">3.4. Clause résolutoire</span> Toute inexécution par l’acheteur de son obligation de paiement pourra entraîner la résolution de plein droit par le vendeur et sans mise en demeure préalable, de toutes les ventes de produits demeurées impayées, des ventes en cours de livraison ainsi que des conditions de règlement préalablement consenties. Toutes autres créances nées deviendront immédiatement exigibles, même si ces dernières ne sont pas échues ou si elles ont donné lieu à des traites. De plus, le vendeur se réserve le droit de subordonner toute nouvelle livraison au règlement préalable des arriérés échus et à un paiement comptant.<br>
En cas de mise en oeuvre de cette clause résolutoire, toutes les sommes perçues resteront acquises définitivement au vendeur à titre d’indemnité forfaitaire en réparation minimum du préjudice subi, sans préjudice de tous autres dommages et intérêts.<br>
                                </p>
                                <p class="footer-bold-left_sub">
ARTICLE 4 – GARANTIES
                                </p>
                                <p class="footer-normal">
Les produits vendus sont garantis contre tout vice provenant d’un défaut de matière, de fabrication ou de conception.<br>
L'acheteur est responsable de la bonne conservation des produits, de leur distribution sous leur conditionnement et dans la limite de leur date de péremption.<br>
Toute intervention de l'acheteur sur le produit, qu'elle soit extrinsèque, sur l’emballage extérieur ou sur tous autres éléments d’informations ou de traçabilité, ou intrinsèque sur ou dans le produit, engage la responsabilité de ce dernier. Il est par conséquent responsable de tous dommages occasionnés aux produits ou à tout tiers du fait des produits ainsi rendus défectueux, ou par toutes autres conditions de stockage ou de conservation défectueuses ou non conformes aux conditions spécifiques du produit ou aux règles de bonnes pratiques. Plus généralement, l’acheteur sera tenu pour responsable de toute action ou omission fautive.<br>
Les produits périmés ne sont ni repris, ni échangés par le vendeur.
                                </p>
                                <p class="footer-bold-left_sub">
ARTICLE 5 – RETRAIT DE PRODUITS
                                </p>
                                <p class="footer-normal">
Compte tenu de la nature des produits, l’acheteur devra exécuter immédiatement toute demande du vendeur ou de son désigné, de retrait de tout ou partie d’un ou plusieurs lots de produits. A défaut de mettre en oeuvre le retrait de produits demandés par le vendeur, l’acheteur engagerait sa responsabilité.
                                </p>
                                <p class="footer-bold-left_sub">
ARTICLE 6 – CAS FORTUIT ET DE FORCE MAJEURE
                                </p>
                                <p class="footer-normal">
Le vendeur est libéré de son obligation de livraison en cas de force majeure ou d’évènements tels que : mobilisation, guerre, grève totale ou partielle, lock out, émeute, acte de terrorisme, réquisition, incendie, inondation, interruption ou retard de transport, manque.
                                </p>
                                <p class="footer-bold-left_sub">
ARTICLE 7 – RESERVE DE PROPRIETE
                                </p>
                                <p class="footer-normal">
Le vendeur conserve la propriété des produits vendus jusqu’au paiement effectif de l’intégralité du prix en principal et accessoire. Dès la livraison, bien qu’il n’en soit pas encore propriétaire, l’acheteur supportera seul tous les risques que les produits livrés pourraient subir ou occasionner ainsi que les charges de l’assurance qu’il s’engage à souscrire à ces fins.<br>
A défaut du paiement dans les conditions précisées à l’article 3, la vente sera résolue de plein droit sans mise en demeure préalable et le produit pourra être revendiqué. En cas de redressement ou de liquidation judiciaire, les commandes en cours seront automatiquement annulées et le vendeur se réserve le droit de revendiquer les marchandises ou stocks conformément aux dispositions légales en vigueur, le cas échéant après inventaire dressé de manière contradictoire avec l’acheteur. Les premiers produits livrés sont présumés être les premiers produits revendus.
                                </p>
                                <p class="footer-bold-left_sub">
ARTICLE 8 - TRIBUNAUX COMPETENTS
                                </p>
                                <p class="footer-normal">
En cas de litige de toute nature ou de contestation relative à la formation ou à l'exécution de la commande, seuls sont compétents les Tribunaux de Paris. Cette clause s'applique même en cas de référé, de demande incidente ou de pluralité de défendeurs.
                                </p>
                                <p class="footer-bold-left_sub">
ARTICLE 9 - DONNEES A CARACTERE PERSONNEL
                                </p>
                                <p class="footer-normal">
<span class="font_sub">Finalité et durée de conservation : </span> le vendeur traitera les données à caractère personnel de l’acheteur et du personnel de celui-ci (telles que le nom, les coordonnées professionnelles, le titre et la fonction) afin de gérer la commande et de se conformer aux exigences réglementaires applicables. Les données à caractère personnel seront conservées aussi longtemps que les droits et obligations contractuels découlant de la commande et les droits et obligations réglementaires applicables pourront être invoquées par ou contre le vendeur.<br>
<span class="font_sub">Transferts internationaux</span>. Aux seules fins susmentionnées, le vendeur peut stocker les données à caractère personnel dans des bases de données centralisées et les communiquer à des filiales de NoName Inc. dans d'autres pays (https://selfservehosteu.noName.com/legal-entities), à ses fournisseurs et aux autorités de contrôle, dans tous les cas partout dans le monde, y compris aux États-Unis. Les États-Unis, comme tout autre pays nonmembre de l'Union Européenne en règle générale, n'ont pas de réglementation sur les données personnelles équivalente à la réglementation européenne. La liste complète des pays non-membres de l'Espace Économique Européen (EEE) ayant des normes de protection des données équivalentes à celles de l'Espace Économique Européen est disponible à l'adresse suivante : http://ec.europa.eu/justice/data-protection/international-transfers/adequacy/index_en.htm.<br>
Le groupe NoName a mis en place des garanties appropriées tant au sein de son groupe qu'avec les fournisseurs non membres de l’EEE et non suisses (tels que les contrats approuvés par les autorités de l'Union Européenne disponibles à l'adresse http://ec.europa.eu/justice/data-protection/internationaltransfers/ transfer/index_en.htm ;et tels qu’ils seront mis à jour de temps à autre par la Commission européenne ou tout autre garantie substantiellement équivalente conformément à la réglementation applicable dans l’EEE et en Suisse. De plus amples informations sur ces garanties peuvent être obtenues en contactant le Délégué à la protection des données, comme indiqué ci-dessous.<br>
<span class="font_sub">Exercice des droits</span>. Les personnes concernées peuvent exercer leur droit d’accès, de rectification, d’effacement, limitation, portabilité et opposition du traitement par email à l’adresse SceClientsDonneesClients@noName.com ou en contactant le délégué à la protection des données (DPO.NoName.com) ou par courrier à NoName, Service Clients, 23-25 avenue du Docteur Lannelongue 75014 Paris. Ces adresses pourront être mises à jour, moyennant un préavis raisonnable. La personne concernée peut introduire une réclamation auprès de l'autorité compétente de protection des données.<br>
                                </p>
                                <p class="footer-bold-left_sub">
ARTICLE 10 - PHARMACOVIGILANCE
                                </p>
                                <p class="footer-normal">
Tout événement indésirable ou toute nouvelle information relative à un événement indésirable collecté relatif à un produit du vendeur sera rapporté immédiatement suivant la prise de connaissance, quelle que soit la gravité de l’événement, de préférence par téléphone ou, à défaut, par fax au service de pharmacovigilance du vendeur :<br>
Téléphone : 01.58.07.33.89<br>
Télécopie : 01.72.26.57.70<br>
E-mail : FRA.AEReporting@noName.com<br>
                                </p>
                                <p class="footer-bold-left_sub">
ARTICLE 11 – DISPOSITIONS GENERALES
                                </p>
                                <p class="footer-normal">
Les produits sont conditionnés aux seules fins de leur vente en France et Monaco. Aucune licence concernant les produits n’est consentie, que ce soit tacitement ou expressément, au sens du droit de la propriété intellectuelle en vigueur, aux Etats-Unis ou dans les autres pays situés en dehors de l’Espace Economique Européen. L’exportation, ou l’autorisation d’exporter, des produits en dehors de l’Espace Européen est susceptible de violer les lois en vigueur aux Etats-Unis et/ou d’autres territoires dans lesquels les produits sont exportés.<br>
L’acheteur n'est en aucun cas autorisé à exporter, directement ou indirectement, les produits dans un quelconque pays à l'extérieur de l'Espace Economique Européen. L’acheteur ne pourra en outre ni vendre, ni transférer, ni distribuer les produits à un tiers dont il sait ou soupçonne qu'il entend ou pourrait exporter les produits en dehors de l'EEE, sans obtenir au préalable de ce tiers un engagement de ne pas procéder à une telle exportation.<br>
En cas de non-respect de cette obligation, le vendeur se réserve le droit de cesser toute vente à l’acheteur. Le fait pour le vendeur de ne pas user de ce droit systématiquement ne saura en aucun cas être interprété comme une renonciation au droit de l'exercer ultérieurement. Aucune renonciation ne pourra être valable à moins d'avoir été écrite et signée.
                                </p>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </body>
</html>







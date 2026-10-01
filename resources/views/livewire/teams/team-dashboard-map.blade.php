<div class="dashboard-map-card">

    @php
        $paymentCount = (int) data_get(
            $mapData,
            'meta.payment_count',
            0
        );

        $customerCount = (int) data_get(
            $mapData,
            'meta.customer_count',
            0
        );

        $mappedRevenue = (int) data_get(
            $mapData,
            'meta.mapped_revenue',
            0
        );

        $mapFeatures = data_get(
            $mapData,
            'features',
            []
        );
    @endphp

    <div class="dashboard-map-header">

        <div>

            <div class="dashboard-map-eyebrow">
                Payment geography
            </div>

            <h2>
                Where payments originated
            </h2>

            <p>
                Approximate payment locations based on IP geolocation.
            </p>

        </div>

        <div class="dashboard-map-badge">
            {{ number_format($paymentCount) }}
            {{ $paymentCount === 1 ? 'payment' : 'payments' }}
        </div>

    </div>


    <div class="dashboard-map-stats">

        <div class="dashboard-map-stat">

            <span>
                Mapped revenue
            </span>

            <strong>
                ${{ number_format($mappedRevenue / 100, 2) }}
            </strong>

        </div>


        <div class="dashboard-map-stat">

            <span>
                Payment locations
            </span>

            <strong>
                {{ number_format($paymentCount) }}
            </strong>

        </div>


        <div class="dashboard-map-stat">

            <span>
                Customers
            </span>

            <strong>
                {{ number_format($customerCount) }}
            </strong>

        </div>

    </div>


 <div class="dashboard-map-wrapper">

    <div
        id="teamDashboardPaymentMap"
        class="dashboard-map"
        wire:ignore
    ></div>


    {{-- =========================================================
         UNSUPPORTED BROWSER / WEBGL 2 FALLBACK
    ========================================================== --}}

    <div
        id="teamDashboardMapUnsupported"
        class="dashboard-map-unsupported"
        wire:ignore
        hidden
    >
        <div class="dashboard-map-unsupported-icon">
            ◉
        </div>

        <strong class="dashboard-map-unsupported-title">
            Map unavailable
        </strong>

        <p class="dashboard-map-unsupported-message">
            This interactive map requires WebGL 2, which is not
            available in your current browser or computer settings.
        </p>


        <div class="dashboard-map-unsupported-card">

            <div class="dashboard-map-unsupported-card-title">
                Recommended: Google Chrome
            </div>

            <div class="dashboard-map-unsupported-card-text">
                For the easiest fix, use the latest version of
                Google Chrome.
            </div>

            <ol class="dashboard-map-help-steps">
                <li>
                    Open <strong>Chrome</strong>.
                </li>

                <li>
                    Click the <strong>three dots ⋮</strong> in the
                    top-right corner.
                </li>

                <li>
                    Open <strong>Settings</strong>.
                </li>

                <li>
                    Open <strong>System</strong>.
                </li>

                <li>
                    Turn on
                    <strong>
                        Use graphics acceleration when available
                    </strong>.
                </li>

                <li>
                    Click <strong>Relaunch</strong>.
                </li>

                <li>
                    Return to this page.
                </li>
            </ol>

        </div>


        <details class="dashboard-map-browser-help">

            <summary>
                I use Firefox
            </summary>

            <div class="dashboard-map-browser-help-content">

                <p>
                    Open Firefox → <strong>Settings</strong> →
                    <strong>General</strong>.
                </p>

                <p>
                    Scroll to <strong>Performance</strong>.
                </p>

                <p>
                    Make sure Firefox can use
                    <strong>hardware acceleration</strong>.
                    You may need to turn off
                    <strong>Use recommended performance settings</strong>
                    to see that option.
                </p>

                <p>
                    Restart Firefox and return to this page.
                </p>

            </div>

        </details>


        <details class="dashboard-map-browser-help">

            <summary>
                I use Microsoft Edge
            </summary>

            <div class="dashboard-map-browser-help-content">

                <p>
                    Open Edge → <strong>Settings</strong> →
                    <strong>System and performance</strong>.
                </p>

                <p>
                    Make sure
                    <strong>
                        Use graphics acceleration when available
                    </strong>
                    is turned on.
                </p>

                <p>
                    Relaunch Edge and return to this page.
                </p>

            </div>

        </details>


        <div class="dashboard-map-unsupported-actions">

            <button
                type="button"
                class="dashboard-map-reload-button"
                onclick="window.location.reload()"
            >
                Reload page
            </button>

        </div>


        <span class="dashboard-map-unsupported-note">
            If the map still does not appear after using a modern
            browser with graphics acceleration enabled, your
            computer may not support WebGL 2.
        </span>

    </div>


    {{-- =========================================================
         NO PAYMENT DATA
    ========================================================== --}}

    @if (empty($mapFeatures))

        <div
            id="teamDashboardMapEmpty"
            class="dashboard-map-empty"
        >
            <div class="dashboard-map-empty-icon">
                ◉
            </div>

            <strong>
                No payment locations
            </strong>

            <span>
                No successful payments with available
                geolocation data exist for this reporting period.
            </span>
        </div>

    @endif

</div>

    <div class="dashboard-map-footer">

        <span class="dashboard-map-footer-dot"></span>

        <span>
            Locations are approximate and may represent
            the customer's network location rather than their
            exact physical location.
        </span>

    </div>


    <style>
        .dashboard-map-card {
            min-width: 0;

            margin-bottom: 14px;
            padding: 21px;

            border: 1px solid var(--dashboard-border);
            border-radius: 17px;

            background: var(--dashboard-surface);
            color: var(--dashboard-text);

            box-shadow: var(--dashboard-shadow);
        }


        .dashboard-map-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;

            gap: 16px;

            margin-bottom: 17px;
        }


        .dashboard-map-eyebrow {
            margin-bottom: 5px;

            color: var(--dashboard-subtle);

            font-size: 10px;
            font-weight: 750;
            letter-spacing: 0.09em;
            text-transform: uppercase;
        }


        .dashboard-map-header h2 {
            margin: 0;

            color: var(--dashboard-text);

            font-size: 16px;
            font-weight: 700;
            letter-spacing: -0.015em;
        }


        .dashboard-map-header p {
            margin: 7px 0 0;

            color: var(--dashboard-muted);

            font-size: 12px;
            line-height: 1.5;
        }


        .dashboard-map-badge {
            flex: 0 0 auto;

            padding: 6px 9px;

            border: 1px solid var(--dashboard-border);
            border-radius: 8px;

            background: var(--dashboard-surface-soft);
            color: var(--dashboard-muted);

            font-size: 10px;
            font-weight: 700;
        }


        .dashboard-map-stats {
            display: grid;

            grid-template-columns:
                repeat(3, minmax(0, 1fr));

            gap: 10px;

            margin-bottom: 14px;
        }


        .dashboard-map-stat {
            min-width: 0;

            padding: 13px 15px;

            border: 1px solid var(--dashboard-border);
            border-radius: 12px;

            background: var(--dashboard-surface-soft);
        }


        .dashboard-map-stat span {
            display: block;

            color: var(--dashboard-muted);

            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }


        .dashboard-map-stat strong {
            display: block;

            margin-top: 5px;

            overflow: hidden;

            color: var(--dashboard-text);

            font-size: 19px;
            font-weight: 800;
            letter-spacing: -0.025em;

            text-overflow: ellipsis;
            white-space: nowrap;
        }


        .dashboard-map-wrapper {
            position: relative;

            width: 100%;
            height: 500px;

            overflow: hidden;

            border: 1px solid var(--dashboard-border);
            border-radius: 14px;

            background:
                var(--dashboard-surface-soft);
        }


        .dashboard-map {
            width: 100%;
            height: 100%;
        }


        .dashboard-map-empty {
            position: absolute;
            inset: 0;

            z-index: 5;

            display: flex;
            align-items: center;
            justify-content: center;

            flex-direction: column;

            padding: 30px;

            background:
                color-mix(
                    in srgb,
                    var(--dashboard-surface) 82%,
                    transparent
                );

            text-align: center;

            pointer-events: none;
        }

        /* ==========================================================================
   Unsupported Browser / WebGL 2
   ========================================================================== */

.dashboard-map-unsupported {
    position: absolute;
    inset: 0;
    z-index: 20;

    display: flex;
    align-items: center;
    justify-content: center;

    flex-direction: column;

    padding: 30px;

    overflow-y: auto;

    background:
        color-mix(
            in srgb,
            var(--dashboard-surface) 95%,
            var(--dashboard-primary) 5%
        );

    text-align: center;
}


/*
|--------------------------------------------------------------------------
| Icon
|--------------------------------------------------------------------------
*/

.dashboard-map-unsupported-icon {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 58px;
    height: 58px;

    margin-bottom: 15px;

    border: 1px solid var(--dashboard-border);
    border-radius: 16px;

    background:
        var(--dashboard-primary-soft);

    color:
        var(--dashboard-primary);

    font-size: 24px;
    font-weight: 800;
}


/*
|--------------------------------------------------------------------------
| Title
|--------------------------------------------------------------------------
*/

.dashboard-map-unsupported-title {
    color:
        var(--dashboard-text);

    font-size: 16px;
    font-weight: 800;
}


/*
|--------------------------------------------------------------------------
| Description
|--------------------------------------------------------------------------
*/

.dashboard-map-unsupported-message {
    max-width: 520px;

    margin: 7px 0 18px;

    color:
        var(--dashboard-muted);

    font-size: 12px;

    line-height: 1.55;
}


/*
|--------------------------------------------------------------------------
| Main instruction card
|--------------------------------------------------------------------------
*/

.dashboard-map-unsupported-card {
    width: min(520px, 100%);

    padding: 17px 18px;

    border: 1px solid var(--dashboard-border);
    border-radius: 13px;

    background:
        var(--dashboard-surface-soft);

    text-align: left;
}


/*
|--------------------------------------------------------------------------
| Card title
|--------------------------------------------------------------------------
*/

.dashboard-map-unsupported-card-title {
    color:
        var(--dashboard-text);

    font-size: 13px;
    font-weight: 750;
}


/*
|--------------------------------------------------------------------------
| Card description
|--------------------------------------------------------------------------
*/

.dashboard-map-unsupported-card-text {
    margin-top: 4px;

    color:
        var(--dashboard-muted);

    font-size: 11px;

    line-height: 1.5;
}


/*
|--------------------------------------------------------------------------
| Steps
|--------------------------------------------------------------------------
*/

.dashboard-map-help-steps {
    display: flex;

    flex-direction: column;

    gap: 7px;

    margin: 13px 0 0;
    padding-left: 21px;

    color:
        var(--dashboard-muted);

    font-size: 11px;

    line-height: 1.5;
}


.dashboard-map-help-steps strong {
    color:
        var(--dashboard-text);

    font-weight: 700;
}


/*
|--------------------------------------------------------------------------
| Browser alternatives
|--------------------------------------------------------------------------
*/

.dashboard-map-browser-help {
    width: min(520px, 100%);

    margin-top: 8px;

    border: 1px solid var(--dashboard-border);
    border-radius: 11px;

    background:
        var(--dashboard-surface-soft);

    text-align: left;
}


.dashboard-map-browser-help summary {
    padding: 12px 14px;

    color:
        var(--dashboard-text);

    font-size: 11px;
    font-weight: 700;

    cursor: pointer;

    user-select: none;
}


.dashboard-map-browser-help-content {
    padding: 0 14px 13px;

    color:
        var(--dashboard-muted);

    font-size: 11px;

    line-height: 1.5;
}


.dashboard-map-browser-help-content p {
    margin: 8px 0 0;
}


.dashboard-map-browser-help-content strong {
    color:
        var(--dashboard-text);

    font-weight: 700;
}


/*
|--------------------------------------------------------------------------
| Reload button
|--------------------------------------------------------------------------
*/

.dashboard-map-unsupported-actions {
    display: flex;

    align-items: center;
    justify-content: center;

    margin-top: 15px;
}


.dashboard-map-reload-button {
    display: inline-flex;

    align-items: center;
    justify-content: center;

    min-height: 38px;

    padding: 0 15px;

    border: 0;
    border-radius: 10px;

    background:
        linear-gradient(
            135deg,
            #8b5cf6,
            #7c3aed
        );

    color: #fff;

    font-size: 12px;
    font-weight: 700;

    cursor: pointer;

    box-shadow:
        0 7px 20px rgba(124, 58, 237, 0.20);

    transition:
        transform 160ms ease,
        box-shadow 160ms ease,
        opacity 160ms ease;
}


.dashboard-map-reload-button:hover {
    transform: translateY(-1px);

    box-shadow:
        0 10px 25px rgba(124, 58, 237, 0.28);
}


/*
|--------------------------------------------------------------------------
| Small note
|--------------------------------------------------------------------------
*/

.dashboard-map-unsupported-note {
    display: block;

    max-width: 520px;

    margin-top: 13px;

    color:
        var(--dashboard-subtle);

    font-size: 10px;

    line-height: 1.5;
}


/*
|--------------------------------------------------------------------------
| Mobile
|--------------------------------------------------------------------------
*/

@media (max-width: 640px) {

    .dashboard-map-unsupported {
        padding: 20px 16px;
    }


    .dashboard-map-unsupported-icon {
        width: 50px;
        height: 50px;

        margin-bottom: 12px;
    }


    .dashboard-map-unsupported-message {
        font-size: 11px;
    }


    .dashboard-map-unsupported-card {
        padding: 15px;
    }

}


        .dashboard-map-empty-icon {
            display: flex;
            align-items: center;
            justify-content: center;

            width: 48px;
            height: 48px;

            margin-bottom: 13px;

            border-radius: 13px;

            background: var(--dashboard-primary-soft);
            color: var(--dashboard-primary);

            font-size: 21px;
            font-weight: 800;
        }


        .dashboard-map-empty strong {
            color: var(--dashboard-text);

            font-size: 14px;
        }


        .dashboard-map-empty span {
            max-width: 340px;

            margin-top: 6px;

            color: var(--dashboard-muted);

            font-size: 12px;
            line-height: 1.5;
        }


        .dashboard-map-footer {
            display: flex;
            align-items: center;

            gap: 8px;

            margin-top: 11px;

            color: var(--dashboard-subtle);

            font-size: 10px;
            line-height: 1.5;
        }


        .dashboard-map-footer-dot {
            width: 6px;
            height: 6px;

            flex: 0 0 6px;

            border-radius: 999px;

            background: var(--dashboard-primary);
        }


        .mapboxgl-popup-content {
            min-width: 220px;

            padding: 15px 16px;

            border-radius: 12px;
        }


        .dashboard-map-popup-title {
            color: #0f172a;

            font-size: 14px;
            font-weight: 750;
        }


        .dashboard-map-popup-amount {
            margin-top: 5px;

            color: #7c3aed;

            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.03em;
        }


        .dashboard-map-popup-location {
            margin-top: 10px;

            color: #475569;

            font-size: 11px;
            line-height: 1.4;
        }


        .dashboard-map-popup-row {
            display: flex;
            justify-content: space-between;

            gap: 14px;

            margin-top: 8px;
            padding-top: 8px;

            border-top: 1px solid #e5e7eb;

            color: #64748b;

            font-size: 10px;
        }


        .dashboard-map-popup-row strong {
            color: #334155;

            font-weight: 700;

            text-align: right;
        }


        @media (max-width: 640px) {

            .dashboard-map-card {
                padding: 16px;
            }


            .dashboard-map-header {
                flex-direction: column;
            }


            .dashboard-map-badge {
                align-self: flex-start;
            }


            .dashboard-map-stats {
                grid-template-columns: 1fr;
            }


            .dashboard-map-wrapper {
                height: 400px;
            }

        }
    </style>


@assets
    <link
        rel="stylesheet"
        href="https://api.mapbox.com/mapbox-gl-js/v3.30.0/mapbox-gl.css"
    >

    <script
        src="https://api.mapbox.com/mapbox-gl-js/v3.30.0/mapbox-gl.js"
        defer
    ></script>
@endassets

  @script
<script>
    (() => {
        const initialData = @js($mapData);

        const mapboxToken = @js(
            config('services.mapbox.public_token')
        );


        /*
        |--------------------------------------------------------------------------
        | Map Manager
        |--------------------------------------------------------------------------
        |
        | The manager intentionally lives on window so it can survive
        | Livewire SPA navigation.
        |
        | IMPORTANT:
        |
        | The Mapbox MAP INSTANCE does NOT survive navigation.
        |
        | The DOM container is replaced during wire:navigate, so we detect
        | when the current container is different and create a new Mapbox
        | instance for it.
        |--------------------------------------------------------------------------
        */

        if (!window.TeamDashboardMapManager) {

            window.TeamDashboardMapManager = (() => {

                let map = null;

                let mapContainer = null;

                let mapData = {
                    type: 'FeatureCollection',
                    features: [],
                    meta: {},
                };


                /*
                |--------------------------------------------------------------------------
                | Helpers
                |--------------------------------------------------------------------------
                */

                function isDarkMode() {
                    return document.documentElement
                        .classList
                        .contains('dark');
                }


                function normalizeData(data) {

                    if (
                        !data ||
                        data.type !== 'FeatureCollection'
                    ) {
                        return {
                            type: 'FeatureCollection',
                            features: [],
                            meta: {},
                        };
                    }


                    return {
                        type: 'FeatureCollection',

                        features:
                            Array.isArray(data.features)
                                ? data.features
                                : [],

                        meta:
                            data.meta ?? {},
                    };
                }


                function escapeHtml(value) {

                    return String(value ?? '')
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                }


                function formatMoney(cents) {

                    return (
                        '$' +
                        (
                            Number(cents ?? 0) / 100
                        ).toLocaleString(
                            undefined,
                            {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2,
                            }
                        )
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Destroy the current Mapbox instance
                |--------------------------------------------------------------------------
                */

                function destroy() {

                    if (map) {

                        try {

                            map.remove();

                        } catch (error) {

                            console.warn(
                                'Unable to remove previous Team Dashboard map.',
                                error
                            );
                        }
                    }


                    map = null;

                    mapContainer = null;
                }


                /*
                |--------------------------------------------------------------------------
                | Get current DOM container
                |--------------------------------------------------------------------------
                */

                function getContainer() {

                    return document.getElementById(
                        'teamDashboardPaymentMap'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Detect whether the existing Map belongs to the current page
                |--------------------------------------------------------------------------
                */

                function hasCurrentContainer() {

                    const container =
                        getContainer();


                    if (!container) {
                        return false;
                    }


                    if (!map) {
                        return false;
                    }


                    if (!mapContainer) {
                        return false;
                    }


                    /*
                    | The most important check.
                    |
                    | wire:navigate creates a new DOM node.
                    |
                    | Even though the ID is identical:
                    |
                    | oldContainer !== newContainer
                    |
                    |--------------------------------------------------------------------------
                    */


                    if (mapContainer !== container) {
                        return false;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Make sure the old container is still actually in the document.
                    |--------------------------------------------------------------------------
                    */

                    if (!document.documentElement.contains(
                        mapContainer
                    )) {
                        return false;
                    }


                    return true;
                }


                /*
                |--------------------------------------------------------------------------
                | Fit map to payment data
                |--------------------------------------------------------------------------
                */

                function fitToData() {

                    if (
                        !map ||
                        !mapData.features.length
                    ) {
                        return;
                    }


                    if (
                        typeof mapboxgl === 'undefined'
                    ) {
                        return;
                    }


                    const bounds =
                        new mapboxgl.LngLatBounds();


                    mapData.features.forEach(
                        feature => {

                            const coordinates =
                                feature
                                    ?.geometry
                                    ?.coordinates;


                            if (
                                Array.isArray(
                                    coordinates
                                ) &&
                                coordinates.length === 2
                            ) {

                                bounds.extend(
                                    coordinates
                                );
                            }

                        }
                    );


                    if (
                        !bounds.isEmpty()
                    ) {

                        map.fitBounds(
                            bounds,
                            {
                                padding: {
                                    top: 70,
                                    bottom: 70,
                                    left: 70,
                                    right: 70,
                                },

                                maxZoom: 12,

                                duration: 700,
                            }
                        );
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Popup HTML
                |--------------------------------------------------------------------------
                */

                function createPopup(feature) {

                    const properties =
                        feature?.properties ?? {};


                    const name =
                        escapeHtml(
                            properties.customer_name
                                || 'Unknown customer'
                        );


                    const amount =
                        formatMoney(
                            properties.amount
                        );


                    const location =
                        escapeHtml(
                            properties.location_label
                                || 'Location unavailable'
                        );


                    const paidAt =
                        escapeHtml(
                            properties.paid_at
                                || 'Unknown'
                        );


                    const paymentMethod =
                        escapeHtml(
                            properties.payment_method
                                || 'Payment'
                        );


                    const accuracy =
                        properties.accuracy_radius
                            ? `~${escapeHtml(
                                properties.accuracy_radius
                            )} km radius`
                            : 'Approximate location';


                    return `
                        <div class="dashboard-map-popup">

                            <div class="dashboard-map-popup-title">
                                ${name}
                            </div>

                            <div class="dashboard-map-popup-amount">
                                ${amount}
                            </div>

                            <div class="dashboard-map-popup-location">
                                ${location}
                            </div>

                            <div class="dashboard-map-popup-row">
                                <span>
                                    Paid
                                </span>

                                <strong>
                                    ${paidAt}
                                </strong>
                            </div>

                            <div class="dashboard-map-popup-row">
                                <span>
                                    Method
                                </span>

                                <strong>
                                    ${paymentMethod}
                                </strong>
                            </div>

                            <div class="dashboard-map-popup-row">
                                <span>
                                    GeoIP
                                </span>

                                <strong>
                                    ${accuracy}
                                </strong>
                            </div>

                        </div>
                    `;
                }


                /*
                |--------------------------------------------------------------------------
                | Add GeoJSON source + layers
                |--------------------------------------------------------------------------
                */

                function addMapData() {

                    if (!map) {
                        return;
                    }


                    if (
                        typeof map.isStyleLoaded !== 'function' ||
                        !map.isStyleLoaded()
                    ) {
                        return;
                    }


                    const sourceId =
                        'team-payment-locations';


                    /*
                    |--------------------------------------------------------------------------
                    | Update existing source
                    |--------------------------------------------------------------------------
                    */

                    const existingSource =
                        map.getSource(
                            sourceId
                        );


                    if (existingSource) {

                        existingSource.setData(
                            mapData
                        );

                        fitToData();

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | GeoJSON source
                    |--------------------------------------------------------------------------
                    */

                    map.addSource(
                        sourceId,
                        {
                            type: 'geojson',

                            data: mapData,

                            cluster: true,

                            clusterMaxZoom: 14,

                            clusterRadius: 50,
                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Cluster circles
                    |--------------------------------------------------------------------------
                    */

                    map.addLayer({

                        id:
                            'team-payment-clusters',

                        type:
                            'circle',

                        source:
                            sourceId,

                        filter: [
                            'has',
                            'point_count',
                        ],

                        paint: {

                            'circle-color': [
                                'step',

                                [
                                    'get',
                                    'point_count',
                                ],

                                '#7c3aed',

                                10,

                                '#2563eb',

                                50,

                                '#0891b2',
                            ],

                            'circle-radius': [
                                'step',

                                [
                                    'get',
                                    'point_count',
                                ],

                                18,

                                10,

                                22,

                                50,

                                27,
                            ],

                            'circle-stroke-width':
                                2,

                            'circle-stroke-color':
                                '#ffffff',

                            'circle-opacity':
                                0.90,
                        },
                    });


                    /*
                    |--------------------------------------------------------------------------
                    | Cluster count
                    |--------------------------------------------------------------------------
                    */

                    map.addLayer({

                        id:
                            'team-payment-cluster-count',

                        type:
                            'symbol',

                        source:
                            sourceId,

                        filter: [
                            'has',
                            'point_count',
                        ],

                        layout: {

                            'text-field':
                                '{point_count_abbreviated}',

                            'text-font': [
                                'Open Sans Bold',
                            ],

                            'text-size':
                                11,
                        },

                        paint: {

                            'text-color':
                                '#ffffff',
                        },
                    });


                    /*
                    |--------------------------------------------------------------------------
                    | Individual payment points
                    |--------------------------------------------------------------------------
                    */

                    map.addLayer({

                        id:
                            'team-payment-points',

                        type:
                            'circle',

                        source:
                            sourceId,

                        filter: [
                            '!',
                            [
                                'has',
                                'point_count',
                            ],
                        ],

                        paint: {

                            'circle-color':
                                isDarkMode()
                                    ? '#a78bfa'
                                    : '#7c3aed',

                            'circle-radius': [
                                'interpolate',

                                ['linear'],

                                [
                                    'get',
                                    'amount',
                                ],

                                0,
                                5,

                                10000,
                                7,

                                100000,
                                10,
                            ],

                            'circle-stroke-width':
                                2,

                            'circle-stroke-color':
                                '#ffffff',

                            'circle-opacity':
                                0.92,
                        },
                    });


                    /*
                    |--------------------------------------------------------------------------
                    | Cluster click
                    |--------------------------------------------------------------------------
                    */

                    map.on(
                        'click',
                        'team-payment-clusters',
                        event => {

                            const features =
                                map.queryRenderedFeatures(
                                    event.point,
                                    {
                                        layers: [
                                            'team-payment-clusters',
                                        ],
                                    }
                                );


                            const clusterId =
                                features[0]
                                    ?.properties
                                    ?.cluster_id;


                            if (
                                clusterId ===
                                undefined
                            ) {
                                return;
                            }


                            const source =
                                map.getSource(
                                    sourceId
                                );


                            if (
                                !source ||
                                typeof source.getClusterExpansionZoom !==
                                    'function'
                            ) {
                                return;
                            }


                            source.getClusterExpansionZoom(
                                clusterId,
                                (
                                    error,
                                    zoom
                                ) => {

                                    if (error) {
                                        return;
                                    }


                                    const coordinates =
                                        features[0]
                                            ?.geometry
                                            ?.coordinates;


                                    if (
                                        !coordinates
                                    ) {
                                        return;
                                    }


                                    map.easeTo({
                                        center:
                                            coordinates,

                                        zoom:
                                            zoom,
                                    });
                                }
                            );
                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Point click
                    |--------------------------------------------------------------------------
                    */

                    map.on(
                        'click',
                        'team-payment-points',
                        event => {

                            const feature =
                                event
                                    .features
                                    ?.[0];


                            if (!feature) {
                                return;
                            }


                            new mapboxgl.Popup({
                                offset: 10,

                                closeButton: true,

                                closeOnClick: true,
                            })
                                .setLngLat(
                                    feature
                                        .geometry
                                        .coordinates
                                )
                                .setHTML(
                                    createPopup(
                                        feature
                                    )
                                )
                                .addTo(
                                    map
                                );
                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Cluster cursor
                    |--------------------------------------------------------------------------
                    */

                    map.on(
                        'mouseenter',
                        'team-payment-clusters',
                        () => {

                            map.getCanvas()
                                .style
                                .cursor =
                                'pointer';
                        }
                    );


                    map.on(
                        'mouseleave',
                        'team-payment-clusters',
                        () => {

                            map.getCanvas()
                                .style
                                .cursor =
                                '';
                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Point cursor
                    |--------------------------------------------------------------------------
                    */

                    map.on(
                        'mouseenter',
                        'team-payment-points',
                        () => {

                            map.getCanvas()
                                .style
                                .cursor =
                                'pointer';
                        }
                    );


                    map.on(
                        'mouseleave',
                        'team-payment-points',
                        () => {

                            map.getCanvas()
                                .style
                                .cursor =
                                '';
                        }
                    );


                    fitToData();
                }


                /*
                |--------------------------------------------------------------------------
                | Update Mapbox basemap theme
                |--------------------------------------------------------------------------
                */

                function updateTheme() {

                    if (
                        !map ||
                        !map.isStyleLoaded()
                    ) {
                        return;
                    }


                    if (
                        typeof map.setConfigProperty ===
                        'function'
                    ) {

                        map.setConfigProperty(
                            'basemap',
                            'lightPreset',

                            isDarkMode()
                                ? 'night'
                                : 'day'
                        );
                    }


                    if (
                        map.getLayer(
                            'team-payment-points'
                        )
                    ) {

                        map.setPaintProperty(
                            'team-payment-points',

                            'circle-color',

                            isDarkMode()
                                ? '#a78bfa'
                                : '#7c3aed'
                        );
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Initialize / Re-initialize
                |--------------------------------------------------------------------------
                */

               async function initialize() {

    /*
    |--------------------------------------------------------------------------
    | Mapbox token
    |--------------------------------------------------------------------------
    */

    if (!mapboxToken) {

        console.error(
            'Mapbox public token is not configured.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Mapbox library
    |--------------------------------------------------------------------------
    */

    if (
        typeof window.mapboxgl ===
        'undefined'
    ) {

        console.error(
            'Mapbox GL JS is not available.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Current page container
    |--------------------------------------------------------------------------
    */

    const container =
        getContainer();


    if (!container) {

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Fallback elements
    |--------------------------------------------------------------------------
    */

    const unsupportedElement =
        document.getElementById(
            'teamDashboardMapUnsupported'
        );


    const emptyElement =
        document.getElementById(
            'teamDashboardMapEmpty'
        );


    /*
    |--------------------------------------------------------------------------
    | WebGL 2 / Mapbox support
    |--------------------------------------------------------------------------
    |
    | Mapbox GL JS v3 requires WebGL 2.
    |
    | mapboxgl.supported() is the official Mapbox compatibility check.
    |--------------------------------------------------------------------------
    */

    if (
        typeof window.mapboxgl.supported !==
        'function' ||
        !window.mapboxgl.supported()
    ) {

        console.warn(
            'Mapbox GL JS is not supported in this browser.'
        );


        /*
        |--------------------------------------------------------------------------
        | Make sure an old map is not left behind
        |--------------------------------------------------------------------------
        */

        destroy();


        /*
        |--------------------------------------------------------------------------
        | Hide normal empty-data message
        |--------------------------------------------------------------------------
        */

        if (emptyElement) {
            emptyElement.hidden = true;
            emptyElement.style.display = 'none';
        }


        /*
        |--------------------------------------------------------------------------
        | Hide map container
        |--------------------------------------------------------------------------
        */

        container.style.display = 'none';


        /*
        |--------------------------------------------------------------------------
        | Show browser instructions
        |--------------------------------------------------------------------------
        */

        if (unsupportedElement) {

            unsupportedElement.hidden = false;

            unsupportedElement.style.display =
                'flex';
        }


        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Browser is supported
    |--------------------------------------------------------------------------
    */

    if (unsupportedElement) {

        unsupportedElement.hidden = true;

        unsupportedElement.style.display =
            'none';
    }


    container.style.display =
        'block';


    /*
    |--------------------------------------------------------------------------
    | If there is no map data, let the normal "No payment locations"
    | overlay remain visible.
    |--------------------------------------------------------------------------
    */

    if (
        emptyElement &&
        !emptyElement.hidden
    ) {

        emptyElement.style.display =
            'flex';
    }


    /*
    |--------------------------------------------------------------------------
    | IMPORTANT:
    |
    | If this exact DOM container already owns the current map,
    | keep the map instead of recreating it.
    |--------------------------------------------------------------------------
    */

    if (
        hasCurrentContainer()
    ) {

        requestAnimationFrame(() => {

            if (
                map &&
                mapContainer ===
                    getContainer()
            ) {

                try {

                    map.resize();

                } catch (error) {

                    console.warn(
                        'Unable to resize Team Dashboard map.',
                        error
                    );
                }
            }
        });


        return;
    }


    /*
    |--------------------------------------------------------------------------
    | SPA navigation replaced the map container.
    |
    | Destroy the old Mapbox instance.
    |--------------------------------------------------------------------------
    */

    destroy();


    /*
    |--------------------------------------------------------------------------
    | Associate manager with NEW container
    |--------------------------------------------------------------------------
    */

    mapContainer =
        container;


    /*
    |--------------------------------------------------------------------------
    | Mapbox token
    |--------------------------------------------------------------------------
    */

    mapboxgl.accessToken =
        mapboxToken;


    /*
    |--------------------------------------------------------------------------
    | Create map
    |--------------------------------------------------------------------------
    */

    const newMap =
        new mapboxgl.Map({
            container:

                container,

            style:
                'mapbox://styles/mapbox/standard',

            center: [
                -95.3698,
                29.7604,
            ],

            zoom: 4,

            config: {

                basemap: {

                    lightPreset:
                        isDarkMode()
                            ? 'night'
                            : 'day',
                },
            },
        });


    map =
        newMap;


    /*
    |--------------------------------------------------------------------------
    | Navigation control
    |--------------------------------------------------------------------------
    */

    newMap.addControl(
        new mapboxgl.NavigationControl({
            visualizePitch: true,
        }),
        'top-right'
    );


    /*
    |--------------------------------------------------------------------------
    | Mapbox error handling
    |--------------------------------------------------------------------------
    */

    newMap.on(
        'error',
        event => {

            console.error(
                'Mapbox error:',
                event?.error ?? event
            );
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Wait for style
    |--------------------------------------------------------------------------
    */

    newMap.once(
        'load',
        () => {

            /*
            |--------------------------------------------------------------------------
            | Ignore stale map instances
            |--------------------------------------------------------------------------
            */

            if (
                map !== newMap
            ) {
                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Make sure this is still the current SPA DOM container
            |--------------------------------------------------------------------------
            */

            if (
                mapContainer !==
                getContainer()
            ) {
                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Add payment data
            |--------------------------------------------------------------------------
            */

            addMapData();


            /*
            |--------------------------------------------------------------------------
            | Apply current theme
            |--------------------------------------------------------------------------
            */

            updateTheme();


            /*
            |--------------------------------------------------------------------------
            | Resize after layout settles
            |--------------------------------------------------------------------------
            */

            requestAnimationFrame(() => {

                if (
                    map === newMap
                ) {

                    try {

                        newMap.resize();

                    } catch (error) {

                        console.warn(
                            'Unable to resize Team Dashboard map.',
                            error
                        );
                    }
                }
            });
        }
    );
}

                /*
                |--------------------------------------------------------------------------
                | Update data
                |--------------------------------------------------------------------------
                */

                function setData(data) {

                    mapData =
                        normalizeData(
                            data
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Update currently displayed map
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !map ||
                        !mapContainer
                    ) {
                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | If navigation has replaced the container, don't attempt
                    | to modify the old map.
                    |--------------------------------------------------------------------------
                    */

                    const currentContainer =
                        getContainer();


                    if (
                        mapContainer !==
                        currentContainer
                    ) {
                        return;
                    }


                    if (
                        !document.documentElement.contains(
                            mapContainer
                        )
                    ) {
                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Map style isn't ready yet.
                    |
                    | The load handler will consume the latest mapData.
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !map.isStyleLoaded()
                    ) {
                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Existing source
                    |--------------------------------------------------------------------------
                    */

                    const source =
                        map.getSource(
                            'team-payment-locations'
                        );


                    if (source) {

                        source.setData(
                            mapData
                        );

                        fitToData();

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Source doesn't exist yet
                    |--------------------------------------------------------------------------
                    */

                    addMapData();
                }


                /*
                |--------------------------------------------------------------------------
                | Public API
                |--------------------------------------------------------------------------
                */

                return {

                    initialize,

                    setData,

                    updateTheme,

                    destroy,

                };

            })();
        }


        /*
        |--------------------------------------------------------------------------
        | Pass initial server data to manager
        |--------------------------------------------------------------------------
        */

        window.TeamDashboardMapManager
            .setData(initialData);


        /*
        |--------------------------------------------------------------------------
        | Initialize for THIS page/component instance
        |--------------------------------------------------------------------------
        |
        | This runs again when Livewire brings the component onto the page.
        | initialize() determines whether the current DOM container is new.
        |--------------------------------------------------------------------------
        */

        window.TeamDashboardMapManager
            .initialize();


        /*
        |--------------------------------------------------------------------------
        | Dashboard map data updates
        |--------------------------------------------------------------------------
        */

        if (
            !window.__teamDashboardMapListener
        ) {

            window.__teamDashboardMapListener =
                true;


            window.addEventListener(
                'dashboard-map-updated',
                event => {

                    const data =
                        event.detail?.data ??
                        null;


                    if (!data) {
                        return;
                    }


                    if (
                        !window.TeamDashboardMapManager
                    ) {
                        return;
                    }


                    window.TeamDashboardMapManager
                        .setData(
                            data
                        );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Theme updates
        |--------------------------------------------------------------------------
        */

        if (
            !window.__teamDashboardMapThemeObserver
        ) {

            window.__teamDashboardMapThemeObserver =
                new MutationObserver(
                    () => {

                        if (
                            window.TeamDashboardMapManager
                        ) {

                            window.TeamDashboardMapManager
                                .updateTheme();
                        }
                    }
                );


            window.__teamDashboardMapThemeObserver
                .observe(
                    document.documentElement,
                    {
                        attributes: true,

                        attributeFilter: [
                            'class',
                            'data-theme',
                        ],
                    }
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Explicit SPA navigation recovery
        |--------------------------------------------------------------------------
        |
        | Livewire fires "livewire:navigated" after navigation completes.
        |
        | This provides an additional safety net for cached/back-forward
        | navigations where the global manager already exists.
        |--------------------------------------------------------------------------
        */

        if (
            !window.__teamDashboardMapNavigateListener
        ) {

            window.__teamDashboardMapNavigateListener =
                true;


            document.addEventListener(
                'livewire:navigated',
                () => {

                    if (
                        !window.TeamDashboardMapManager
                    ) {
                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Give Livewire one frame to finish the final DOM swap.
                    |--------------------------------------------------------------------------
                    */

                    requestAnimationFrame(() => {

                        requestAnimationFrame(() => {

                            window.TeamDashboardMapManager
                                .initialize();

                        });

                    });
                }
            );
        }

    })();
</script>
@endscript

</div>
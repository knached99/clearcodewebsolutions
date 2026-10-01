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


        @if (empty($mapFeatures))

            <div class="dashboard-map-empty">

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


    <script>
        (() => {

            const initialData = @js($mapData);

            const mapboxToken =
                @js(
                    config(
                        'services.mapbox.public_token'
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | Map Manager
            |--------------------------------------------------------------------------
            */

            if (!window.TeamDashboardMapManager) {

                window.TeamDashboardMapManager = (() => {

                    let map = null;

                    let initialized = false;

                    let mapboxLoadPromise = null;

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

                        return String(
                            value ?? ''
                        )
                            .replace(
                                /&/g,
                                '&amp;'
                            )
                            .replace(
                                /</g,
                                '&lt;'
                            )
                            .replace(
                                />/g,
                                '&gt;'
                            )
                            .replace(
                                /"/g,
                                '&quot;'
                            )
                            .replace(
                                /'/g,
                                '&#039;'
                            );
                    }


                    function formatMoney(cents) {

                        return (
                            '$' +
                            (
                                Number(cents ?? 0) /
                                100
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
                    | Mapbox Loader
                    |--------------------------------------------------------------------------
                    */

                    function ensureMapbox() {

                        if (
                            typeof window.mapboxgl !==
                            'undefined'
                        ) {
                            return Promise.resolve();
                        }


                        if (mapboxLoadPromise) {
                            return mapboxLoadPromise;
                        }


                        mapboxLoadPromise =
                            new Promise(
                                (
                                    resolve,
                                    reject
                                ) => {

                                    const existingScript =
                                        document.querySelector(
                                            'script[data-team-dashboard-mapbox]'
                                        );


                                    const existingCss =
                                        document.querySelector(
                                            'link[data-team-dashboard-mapbox-css]'
                                        );


                                    if (!existingCss) {

                                        const css =
                                            document.createElement(
                                                'link'
                                            );

                                        css.rel = 'stylesheet';

                                        css.href =
                                            'https://api.mapbox.com/mapbox-gl-js/v3.30.0/mapbox-gl.css';

                                        css.dataset
                                            .teamDashboardMapboxCss =
                                            'true';

                                        document.head.appendChild(
                                            css
                                        );
                                    }


                                    if (existingScript) {

                                        existingScript.addEventListener(
                                            'load',
                                            () => resolve()
                                        );

                                        existingScript.addEventListener(
                                            'error',
                                            reject
                                        );

                                        return;
                                    }


                                    const script =
                                        document.createElement(
                                            'script'
                                        );

                                    script.src =
                                        'https://api.mapbox.com/mapbox-gl-js/v3.30.0/mapbox-gl.js';

                                    script.dataset
                                        .teamDashboardMapbox =
                                        'true';

                                    script.onload =
                                        () => resolve();

                                    script.onerror =
                                        reject;

                                    document.head.appendChild(
                                        script
                                    );

                                }
                            );

                        return mapboxLoadPromise;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Fit Map
                    |--------------------------------------------------------------------------
                    */

                    function fitToData() {

                        if (
                            !map ||
                            !mapData.features.length
                        ) {
                            return;
                        }


                        const bounds =
                            new mapboxgl.LngLatBounds();


                        mapData.features
                            .forEach(
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
                    | Popup
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


                        const html = `

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


                        return html;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Add Sources / Layers
                    |--------------------------------------------------------------------------
                    */

                    function addMapData() {

                        if (
                            !map ||
                            !map.isStyleLoaded()
                        ) {
                            return;
                        }


                        const sourceId =
                            'team-payment-locations';


                        /*
                        |--------------------------------------------------------------------------
                        | Existing source
                        |--------------------------------------------------------------------------
                        */

                        if (
                            map.getSource(
                                sourceId
                            )
                        ) {

                            map.getSource(
                                sourceId
                            ).setData(
                                mapData
                            );

                            return;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | GeoJSON source
                        |--------------------------------------------------------------------------
                        |
                        | Mapbox clustering keeps large numbers of payment
                        | points performant.
                        |
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
                            id: 'team-payment-clusters',

                            type: 'circle',

                            source: sourceId,

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

                                'circle-stroke-width': 2,

                                'circle-stroke-color':
                                    '#ffffff',

                                'circle-opacity': 0.90,

                            },
                        });


                        /*
                        |--------------------------------------------------------------------------
                        | Cluster count
                        |--------------------------------------------------------------------------
                        */

                        map.addLayer({
                            id: 'team-payment-cluster-count',

                            type: 'symbol',

                            source: sourceId,

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

                                'text-size': 11,

                            },

                            paint: {

                                'text-color':
                                    '#ffffff',

                            },

                        });


                        /*
                        |--------------------------------------------------------------------------
                        | Individual points
                        |--------------------------------------------------------------------------
                        */

                        map.addLayer({
                            id: 'team-payment-points',

                            type: 'circle',

                            source: sourceId,

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

                                'circle-stroke-width': 2,

                                'circle-stroke-color':
                                    '#ffffff',

                                'circle-opacity': 0.92,

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


                                map.getSource(
                                    sourceId
                                )
                                    .getClusterExpansionZoom(
                                        clusterId,
                                        (
                                            error,
                                            zoom
                                        ) => {

                                            if (
                                                error
                                            ) {
                                                return;
                                            }


                                            const coordinates =
                                                features[0]
                                                    .geometry
                                                    .coordinates;


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
                        | Cursor
                        |--------------------------------------------------------------------------
                        */

                        map.on(
                            'mouseenter',
                            'team-payment-clusters',
                            () => {
                                map.getCanvas()
                                    .style
                                    .cursor = 'pointer';
                            }
                        );


                        map.on(
                            'mouseleave',
                            'team-payment-clusters',
                            () => {
                                map.getCanvas()
                                    .style
                                    .cursor = '';
                            }
                        );


                        map.on(
                            'mouseenter',
                            'team-payment-points',
                            () => {
                                map.getCanvas()
                                    .style
                                    .cursor = 'pointer';
                            }
                        );


                        map.on(
                            'mouseleave',
                            'team-payment-points',
                            () => {
                                map.getCanvas()
                                    .style
                                    .cursor = '';
                            }
                        );


                        fitToData();

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Update Basemap Theme
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
                    | Initialize
                    |--------------------------------------------------------------------------
                    */

                    async function initialize() {

                        if (initialized) {
                            return;
                        }


                        if (!mapboxToken) {

                            console.error(
                                'Mapbox public token is not configured.'
                            );

                            return;
                        }


                        await ensureMapbox();


                        if (
                            typeof window.mapboxgl ===
                            'undefined'
                        ) {
                            return;
                        }


                        mapboxgl.accessToken =
                            mapboxToken;


                        const container =
                            document.getElementById(
                                'teamDashboardPaymentMap'
                            );


                        if (!container) {
                            return;
                        }


                        map =
                            new mapboxgl.Map({
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


                        map.addControl(
                            new mapboxgl.NavigationControl({
                                visualizePitch:
                                    true,
                            }),
                            'top-right'
                        );


                        map.on(
                            'load',
                            () => {

                                addMapData();

                                updateTheme();

                            }
                        );


                        initialized = true;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Update Data
                    |--------------------------------------------------------------------------
                    */

                    function setData(data) {

                        mapData =
                            normalizeData(
                                data
                            );


                        if (
                            map &&
                            map.isStyleLoaded()
                        ) {

                            const source =
                                map.getSource(
                                    'team-payment-locations'
                                );


                            if (source) {

                                source.setData(
                                    mapData
                                );


                                fitToData();

                            } else {

                                addMapData();

                            }

                        }

                    }


                    return {
                        initialize,
                        setData,
                        updateTheme,
                    };

                })();

            }


            /*
            |--------------------------------------------------------------------------
            | Initial Data
            |--------------------------------------------------------------------------
            */

            window.TeamDashboardMapManager
                .setData(
                    initialData
                );


            /*
            |--------------------------------------------------------------------------
            | Initialize
            |--------------------------------------------------------------------------
            */

            window.TeamDashboardMapManager
                .initialize();


            /*
            |--------------------------------------------------------------------------
            | Livewire Map Updates
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
                            event.detail?.data
                            ?? null;


                        if (!data) {
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
            | Dashboard Theme Updates
            |--------------------------------------------------------------------------
            */

            if (
                !window.__teamDashboardMapThemeObserver
            ) {

                window.__teamDashboardMapThemeObserver =
                    new MutationObserver(
                        () => {

                            window
                                .TeamDashboardMapManager
                                ?.updateTheme();

                        }
                    );


                window.__teamDashboardMapThemeObserver
                    .observe(
                        document.documentElement,
                        {
                            attributes: true,

                            attributeFilter: [
                                'class',
                            ],
                        }
                    );

            }

        })();
    </script>

</div>
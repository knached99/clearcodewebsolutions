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

        const mapboxToken = @js(
            config('services.mapbox.public_token')
        );

        const sourceId = 'team-payment-locations';

        const clusterLayerId = 'team-payment-clusters';
        const clusterCountLayerId = 'team-payment-cluster-count';
        const pointLayerId = 'team-payment-points';

        const clusterImageId = 'team-payment-cluster-image';
        const pointImageId = 'team-payment-point-image';

        const PURPLE = '#a78bfa';

        /*
        |--------------------------------------------------------------------------
        | Remove previous map manager / map instance
        |--------------------------------------------------------------------------
        */

        try {
            if (
                window.TeamDashboardMapManager &&
                typeof window.TeamDashboardMapManager.destroy === 'function'
            ) {
                window.TeamDashboardMapManager.destroy();
            }
        } catch (error) {
            // Ignore cleanup errors.
        }

        try {
            if (
                window.__teamDashboardMap &&
                typeof window.__teamDashboardMap.remove === 'function'
            ) {
                window.__teamDashboardMap.remove();
            }
        } catch (error) {
            // Ignore cleanup errors.
        }

        delete window.TeamDashboardMapManager;
        delete window.__teamDashboardMap;

        /*
        |--------------------------------------------------------------------------
        | Map Manager
        |--------------------------------------------------------------------------
        */

        window.TeamDashboardMapManager = (() => {
            let map = null;
            let initialized = false;
            let mapboxLoadPromise = null;
            let interactionsBound = false;

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
                return document.documentElement.classList.contains('dark');
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
                    features: Array.isArray(data.features)
                        ? data.features
                        : [],
                    meta: data.meta ?? {},
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
            | Mapbox Loader
            |--------------------------------------------------------------------------
            */

            function ensureMapbox() {
                if (
                    typeof window.mapboxgl !== 'undefined'
                ) {
                    return Promise.resolve();
                }

                if (mapboxLoadPromise) {
                    return mapboxLoadPromise;
                }

                mapboxLoadPromise = new Promise(
                    (resolve, reject) => {
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
                                document.createElement('link');

                            css.rel = 'stylesheet';

                            css.href =
                                'https://api.mapbox.com/mapbox-gl-js/v3.30.0/mapbox-gl.css';

                            css.dataset.teamDashboardMapboxCss =
                                'true';

                            document.head.appendChild(css);
                        }

                        if (existingScript) {
                            if (
                                typeof window.mapboxgl !==
                                'undefined'
                            ) {
                                resolve();
                                return;
                            }

                            existingScript.addEventListener(
                                'load',
                                resolve,
                                { once: true }
                            );

                            existingScript.addEventListener(
                                'error',
                                reject,
                                { once: true }
                            );

                            return;
                        }

                        const script =
                            document.createElement('script');

                        script.src =
                            'https://api.mapbox.com/mapbox-gl-js/v3.30.0/mapbox-gl.js';

                        script.dataset.teamDashboardMapbox =
                            'true';

                        script.onload = resolve;
                        script.onerror = reject;

                        document.head.appendChild(script);
                    }
                );

                return mapboxLoadPromise;
            }

            /*
            |--------------------------------------------------------------------------
            | Marker Image Creation
            |--------------------------------------------------------------------------
            */

            function createCircleImage(
                size,
                fillColor,
                strokeColor,
                strokeWidth
            ) {
                const canvas =
                    document.createElement('canvas');

                canvas.width = size;
                canvas.height = size;

                const context =
                    canvas.getContext('2d');

                if (!context) {
                    throw new Error(
                        'Unable to create canvas context.'
                    );
                }

                const center = size / 2;
                const radius = (size / 2) - strokeWidth;

                context.clearRect(
                    0,
                    0,
                    size,
                    size
                );

                context.beginPath();

                context.arc(
                    center,
                    center,
                    radius,
                    0,
                    Math.PI * 2
                );

                context.fillStyle =
                    fillColor;

                context.fill();

                context.lineWidth =
                    strokeWidth;

                context.strokeStyle =
                    strokeColor;

                context.stroke();

                return {
                    width: size,
                    height: size,
                    data: context.getImageData(
                        0,
                        0,
                        size,
                        size
                    ).data,
                };
            }

            /*
            |--------------------------------------------------------------------------
            | Register Marker Images
            |--------------------------------------------------------------------------
            */

            function ensureMarkerImages() {
                if (!map) {
                    return;
                }

                /*
                | Individual payment marker
                */

                if (!map.hasImage(pointImageId)) {
                    const pointImage =
                        createCircleImage(
                            64,
                            PURPLE,
                            '#ffffff',
                            5
                        );

                    map.addImage(
                        pointImageId,
                        pointImage,
                        {
                            pixelRatio: 2,
                        }
                    );
                }

                /*
                | Cluster marker
                */

                if (!map.hasImage(clusterImageId)) {
                    const clusterImage =
                        createCircleImage(
                            96,
                            PURPLE,
                            '#ffffff',
                            6
                        );

                    map.addImage(
                        clusterImageId,
                        clusterImage,
                        {
                            pixelRatio: 2,
                        }
                    );
                }
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

                mapData.features.forEach(
                    (feature) => {
                        const coordinates =
                            feature?.geometry?.coordinates;

                        if (
                            Array.isArray(coordinates) &&
                            coordinates.length === 2
                        ) {
                            bounds.extend(
                                coordinates
                            );
                        }
                    }
                );

                if (!bounds.isEmpty()) {
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
                        properties.customer_name ||
                        'Unknown customer'
                    );

                const amount =
                    formatMoney(
                        properties.amount
                    );

                const location =
                    escapeHtml(
                        properties.location_label ||
                        'Location unavailable'
                    );

                const paidAt =
                    escapeHtml(
                        properties.paid_at ||
                        'Unknown'
                    );

                const paymentMethod =
                    escapeHtml(
                        properties.payment_method ||
                        'Payment'
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
                            <span>Paid</span>
                            <strong>${paidAt}</strong>
                        </div>

                        <div class="dashboard-map-popup-row">
                            <span>Method</span>
                            <strong>${paymentMethod}</strong>
                        </div>

                        <div class="dashboard-map-popup-row">
                            <span>GeoIP</span>
                            <strong>${accuracy}</strong>
                        </div>
                    </div>
                `;
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

                /*
                |--------------------------------------------------------------------------
                | GeoJSON Source
                |--------------------------------------------------------------------------
                */

                if (!map.getSource(sourceId)) {
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
                } else {
                    map.getSource(sourceId).setData(
                        mapData
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Marker Images
                |--------------------------------------------------------------------------
                */

                ensureMarkerImages();

                /*
                |--------------------------------------------------------------------------
                | Cluster Marker
                |--------------------------------------------------------------------------
                |
                | Image-based symbol instead of a circle layer.
                |
                */

                if (!map.getLayer(clusterLayerId)) {
                    map.addLayer({
                        id:
                            clusterLayerId,

                        type:
                            'symbol',

                        source:
                            sourceId,

                        filter: [
                            'has',
                            'point_count',
                        ],

                        layout: {
                            'icon-image':
                                clusterImageId,

                            'icon-size': [
                                'step',

                                [
                                    'get',
                                    'point_count',
                                ],

                                0.72,

                                10,
                                0.85,

                                50,
                                1.00,
                            ],

                            'icon-allow-overlap':
                                true,

                            'icon-ignore-placement':
                                true,

                            'text-field':
                                '{point_count_abbreviated}',

                            'text-font': [
                                'Open Sans Bold',
                            ],

                            'text-size':
                                12,

                            'text-allow-overlap':
                                true,

                            'text-ignore-placement':
                                true,
                        },

                        paint: {
                            'text-color':
                                '#ffffff',

                            'text-halo-color':
                                PURPLE,

                            'text-halo-width':
                                0.5,
                        },
                    });
                }

                /*
                |--------------------------------------------------------------------------
                | Cluster Count
                |--------------------------------------------------------------------------
                |
                | Kept for compatibility. Hidden visually because the cluster
                | layer above already contains the count.
                |
                */

                if (
                    !map.getLayer(
                        clusterCountLayerId
                    )
                ) {
                    map.addLayer({
                        id:
                            clusterCountLayerId,

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
                                '',

                            'text-size':
                                1,
                        },
                    });
                }

                /*
                |--------------------------------------------------------------------------
                | Individual Payment Marker
                |--------------------------------------------------------------------------
                */

                if (!map.getLayer(pointLayerId)) {
                    map.addLayer({
                        id:
                            pointLayerId,

                        type:
                            'symbol',

                        source:
                            sourceId,

                        filter: [
                            '!',
                            [
                                'has',
                                'point_count',
                            ],
                        ],

                        layout: {
                            'icon-image':
                                pointImageId,

                            'icon-size':
                                0.70,

                            'icon-allow-overlap':
                                true,

                            'icon-ignore-placement':
                                true,
                        },
                    });
                }

                /*
                |--------------------------------------------------------------------------
                | Interactions
                |--------------------------------------------------------------------------
                */

                bindInteractions();

                fitToData();
            }

            /*
            |--------------------------------------------------------------------------
            | Interactions
            |--------------------------------------------------------------------------
            */

            function bindInteractions() {
                if (
                    !map ||
                    interactionsBound
                ) {
                    return;
                }

                interactionsBound = true;

                /*
                |--------------------------------------------------------------------------
                | Cluster Click
                |--------------------------------------------------------------------------
                */

                map.on(
                    'click',
                    clusterLayerId,
                    (event) => {
                        const features =
                            map.queryRenderedFeatures(
                                event.point,
                                {
                                    layers: [
                                        clusterLayerId,
                                    ],
                                }
                            );

                        const clusterId =
                            features[0]
                                ?.properties
                                ?.cluster_id;

                        if (
                            clusterId === undefined
                        ) {
                            return;
                        }

                        const coordinates =
                            features[0]
                                ?.geometry
                                ?.coordinates;

                        if (!coordinates) {
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
                | Individual Point Click
                |--------------------------------------------------------------------------
                */

                map.on(
                    'click',
                    pointLayerId,
                    (event) => {
                        const feature =
                            event.features?.[0];

                        if (!feature) {
                            return;
                        }

                        new mapboxgl.Popup({
                            offset:
                                10,

                            closeButton:
                                true,

                            closeOnClick:
                                true,
                        })
                            .setLngLat(
                                feature.geometry
                                    .coordinates
                            )
                            .setHTML(
                                createPopup(
                                    feature
                                )
                            )
                            .addTo(map);
                    }
                );

                /*
                |--------------------------------------------------------------------------
                | Cluster Cursor
                |--------------------------------------------------------------------------
                */

                map.on(
                    'mouseenter',
                    clusterLayerId,
                    () => {
                        map.getCanvas()
                            .style
                            .cursor =
                            'pointer';
                    }
                );

                map.on(
                    'mouseleave',
                    clusterLayerId,
                    () => {
                        map.getCanvas()
                            .style
                            .cursor =
                            '';
                    }
                );

                /*
                |--------------------------------------------------------------------------
                | Point Cursor
                |--------------------------------------------------------------------------
                */

                map.on(
                    'mouseenter',
                    pointLayerId,
                    () => {
                        map.getCanvas()
                            .style
                            .cursor =
                            'pointer';
                    }
                );

                map.on(
                    'mouseleave',
                    pointLayerId,
                    () => {
                        map.getCanvas()
                            .style
                            .cursor =
                            '';
                    }
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Theme
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

                requestAnimationFrame(() => {
                    ensureMarkerImages();
                });
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

                try {
                    await ensureMapbox();
                } catch (error) {
                    console.error(
                        'Failed to load Mapbox GL JS.',
                        error
                    );

                    return;
                }

                if (
                    typeof window.mapboxgl ===
                    'undefined'
                ) {
                    console.error(
                        'Mapbox GL JS is unavailable.'
                    );

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

                /*
                |--------------------------------------------------------------------------
                | Create Map
                |--------------------------------------------------------------------------
                */

                map =
                    new mapboxgl.Map({
                        container,

                        style:
                            'mapbox://styles/mapbox/standard',

                        center: [
                            -95.3698,
                            29.7604,
                        ],

                        zoom:
                            4,

                        config: {
                            basemap: {
                                lightPreset:
                                    isDarkMode()
                                        ? 'night'
                                        : 'day',
                            },
                        },
                    });

                window.__teamDashboardMap =
                    map;

                /*
                |--------------------------------------------------------------------------
                | Navigation Control
                |--------------------------------------------------------------------------
                */

                map.addControl(
                    new mapboxgl.NavigationControl({
                        visualizePitch:
                            true,
                    }),
                    'top-right'
                );

                /*
                |--------------------------------------------------------------------------
                | Map Load
                |--------------------------------------------------------------------------
                */

                map.on(
                    'load',
                    () => {
                        ensureMarkerImages();

                        addMapData();

                        updateTheme();

                        bindInteractions();
                    }
                );

                /*
                |--------------------------------------------------------------------------
                | Style Changes
                |--------------------------------------------------------------------------
                */

                map.on(
                    'styledata',
                    () => {
                        if (
                            !map ||
                            !map.isStyleLoaded()
                        ) {
                            return;
                        }

                        ensureMarkerImages();

                        const sourceExists =
                            !!map.getSource(
                                sourceId
                            );

                        const pointLayerExists =
                            !!map.getLayer(
                                pointLayerId
                            );

                        const clusterLayerExists =
                            !!map.getLayer(
                                clusterLayerId
                            );

                        if (
                            !sourceExists ||
                            !pointLayerExists ||
                            !clusterLayerExists
                        ) {
                            addMapData();
                        }
                    }
                );

                initialized =
                    true;
            }

            /*
            |--------------------------------------------------------------------------
            | Update Data
            |--------------------------------------------------------------------------
            */

            function setData(data) {
                mapData =
                    normalizeData(data);

                if (
                    !map ||
                    !map.isStyleLoaded()
                ) {
                    return;
                }

                const source =
                    map.getSource(
                        sourceId
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

            /*
            |--------------------------------------------------------------------------
            | Destroy
            |--------------------------------------------------------------------------
            */

            function destroy() {
                if (map) {
                    try {
                        map.remove();
                    } catch (error) {
                        // Ignore cleanup errors.
                    }
                }

                map = null;
                initialized = false;
                interactionsBound = false;
            }

            return {
                initialize,
                setData,
                updateTheme,
                destroy,
            };
        })();

        /*
        |--------------------------------------------------------------------------
        | Initial Data
        |--------------------------------------------------------------------------
        */

        window.TeamDashboardMapManager.setData(
            initialData
        );

        /*
        |--------------------------------------------------------------------------
        | Initialize
        |--------------------------------------------------------------------------
        */

        window.TeamDashboardMapManager.initialize();

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
                (event) => {
                    const data =
                        event.detail?.data ??
                        null;

                    if (!data) {
                        return;
                    }

                    window.TeamDashboardMapManager
                        ?.setData(
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
                        window.TeamDashboardMapManager
                            ?.updateTheme();
                    }
                );

            window.__teamDashboardMapThemeObserver.observe(
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
from python_api.registry import ALL_ROUTES


def test_route_manifest_count():
    assert len(ALL_ROUTES) == 115


def test_routes_unique():
    keys = {(route.w, route.r) for route in ALL_ROUTES}
    assert len(keys) == len(ALL_ROUTES)

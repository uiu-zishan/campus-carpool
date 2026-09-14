#!/bin/bash
# P4: Concurrency Test — proves SELECT FOR UPDATE prevents overbooking
# 
# USAGE: bash tests/concurrency_test.sh <ride_id> <test_user_count>
#
# Prerequisites:
#   - XAMPP running (Apache + MySQL)
#   - A test ride with exactly 1 seat available
#   - N test users already registered

RIDE_ID=${1:-1}
USER_COUNT=${2:-10}

echo "=== Concurrency Test: $USER_COUNT users booking ride #$RIDE_ID ==="
echo ""

# Fire N parallel curl requests
for i in $(seq 1 $USER_COUNT); do
    (
        curl -s -X POST \
            -H "Content-Type: application/json" \
            -d "{\"ride_id\":$RIDE_ID, \"pickup_checkpoint_id\":1, \"dropoff_checkpoint_id\":2}" \
            http://localhost/campus-carpool/api/book_ride.php \
            | grep -o '"success":[a-z]*'
    ) &
done

wait
echo ""
echo "=== Test complete. Check: only 1 booking should have succeeded. ==="
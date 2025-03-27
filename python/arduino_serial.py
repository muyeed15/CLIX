import serial
import serial.tools.list_ports
import time
import re
import logging

# Configure logging
logging.basicConfig(level=logging.INFO, 
                    format='%(asctime)s - %(levelname)s - %(message)s',
                    filename='serial_reader.log')

def list_serial_ports():
    ports = serial.tools.list_ports.comports()
    return [port.device for port in ports]

def select_port():
    available_ports = list_serial_ports()
    if not available_ports:
        logging.warning("No serial ports found.")
        print("No serial ports found.")
        return None
    
    print("Available serial ports:")
    for i, port in enumerate(available_ports):
        print(f"{i + 1}: {port}")
    
    while True:
        try:
            choice = int(input("Select a port (enter the number): "))
            if 1 <= choice <= len(available_ports):
                return available_ports[choice - 1]
            else:
                print("Invalid choice. Please select a valid number.")
        except ValueError:
            print("Please enter a number.")

def process_data(data):
    try:
        # Extract RSSI and other values
        rssi_match = re.search(r'RSSI:(-?\d+) dBm', data)
        values_match = re.search(r'Message: Ampere:([-\d.]+), Watt:([-\d.]+), Watt-hour:([-\d.]+)', data)
        
        if rssi_match and values_match:
            rssi = rssi_match.group(1)
            ampere = max(0, float(values_match.group(1)))
            watt = max(0, float(values_match.group(2)))
            watt_hour = max(0, float(values_match.group(3)))
            
            # Reconstruct the message with processed values
            processed_data = f"RSSI:{rssi} dBm | Message: Ampere:{ampere:.2f}, Watt:{watt:.2f}, Watt-hour:{watt_hour:.4f}"
            return processed_data
        
        # If formatting doesn't match, log the issue but return original data
        logging.warning(f"Unexpected data format: {data}")
        return data
    
    except (ValueError, TypeError) as e:
        logging.error(f"Error processing data: {e}. Raw data: {data}")
        return data

def main():
    port = select_port()
    if not port:
        return

    baud_rate = 9600
    file_path = "./data/arduino_last_output.txt"

    try:
        ser = serial.Serial(port, baud_rate, timeout=1)
        time.sleep(2)
        logging.info(f"Connected to port {port}")

        print("Reading from Arduino...")
        while True:
            try:
                if ser.in_waiting > 0:
                    # Try multiple decoding methods
                    try:
                        # First, try UTF-8 decoding
                        raw_data = ser.readline().decode("utf-8").strip()
                    except UnicodeDecodeError:
                        try:
                            # If UTF-8 fails, try latin-1 (which never fails)
                            raw_data = ser.readline().decode("latin-1").strip()
                            logging.warning("Fallback to latin-1 decoding")
                        except Exception as decode_error:
                            logging.error(f"Decoding error: {decode_error}")
                            continue

                    # Process the data
                    processed_data = process_data(raw_data)
                    print(processed_data)
                    
                    try:
                        with open(file_path, "w") as file:
                            file.write(processed_data)
                    except IOError as file_error:
                        logging.error(f"File writing error: {file_error}")

            except serial.SerialException as serial_error:
                logging.error(f"Serial error: {serial_error}")
                time.sleep(1)  # Prevent tight error loops
                continue

            time.sleep(0.1)  # Small delay to prevent CPU overuse

    except serial.SerialException as e:
        logging.error(f"Initial Serial Connection Error: {e}")
        print(f"Error connecting to serial port: {e}")
    except KeyboardInterrupt:
        logging.info("Stopped by user.")
        print("\nStopped by user.")
    finally:
        if 'ser' in locals() and ser.is_open:
            ser.close()
            logging.info("Serial port closed")

if __name__ == "__main__":
    main()
#!/usr/bin/env python3
import csv
import json
import re

def fix_json_string(json_str):
    """Fix malformed JSON string by removing extra escaping and quotes"""
    if not json_str or json_str.strip() == '':
        return None
    
    # Remove the outer quotes if they exist
    json_str = json_str.strip()
    if json_str.startswith('"') and json_str.endswith('"'):
        json_str = json_str[1:-1]
    
    # Replace escaped quotes with regular quotes
    json_str = json_str.replace('\\"', '"')
    
    # Remove any remaining backslashes before quotes
    json_str = re.sub(r'\\+(")', r'\1', json_str)
    
    try:
        # Try to parse the JSON to validate it
        parsed = json.loads(json_str)
        # Return the properly formatted JSON string
        return json.dumps(parsed, separators=(',', ':'))
    except json.JSONDecodeError as e:
        print(f"Error parsing JSON: {e}")
        print(f"Problematic string: {json_str[:100]}...")
        return None

def process_csv(input_file, output_file):
    """Process the CSV file and fix the JSON in set_details column"""
    with open(input_file, 'r', encoding='utf-8') as infile, \
         open(output_file, 'w', encoding='utf-8', newline='') as outfile:
        
        reader = csv.DictReader(infile)
        writer = csv.DictWriter(outfile, fieldnames=reader.fieldnames)
        
        # Write header
        writer.writeheader()
        
        # Process each row
        for row_num, row in enumerate(reader, start=2):  # Start at 2 because row 1 is header
            # Fix the set_details column
            if 'set_details' in row:
                fixed_json = fix_json_string(row['set_details'])
                if fixed_json is not None:
                    row['set_details'] = fixed_json
                else:
                    print(f"Warning: Could not fix JSON in row {row_num}, setting to NULL")
                    row['set_details'] = ''
            
            writer.writerow(row)
        
        print(f"Processed {row_num - 1} rows")
        print(f"Fixed CSV saved to: {output_file}")

if __name__ == "__main__":
    input_file = "workout_plan_schedule_test.csv"
    output_file = "workout_plan_schedule_fixed.csv"
    
    process_csv(input_file, output_file) 
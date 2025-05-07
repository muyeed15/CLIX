#!/usr/bin/env python3
# clix_analytics.py

import mysql.connector
import json
import os
import time
import requests
import pandas as pd
import matplotlib.pyplot as plt
import seaborn as sns
from datetime import datetime, timedelta

# Configuration
# Update the CONFIG section in clix_analytics.py to use the correct paths

CONFIG = {
    'database': {
        'host': 'localhost',
        'user': 'root',
        'password': '',
        'database': 'clix_database'
    },
    'lm_studio': {
        'api_base': 'http://localhost:1234/v1',  # Default LM Studio API endpoint
        'model': 'hermes-3-llama-3.2-3b'
    },
    'output': {
        'dir': '../data',  # Corrected path to data directory
        'data_file': 'analytics_data.json',
        'summary_file': 'analytics_summary.json',
        'graph_dir': 'graphs'
    }
}

class CLIXAnalytics:
    def __init__(self, config=CONFIG):
        self.config = config
        self.data = {}
        self.summary = {}
        self.ensure_output_directories()
        
    def ensure_output_directories(self):
        """Ensure output directories exist"""
        os.makedirs(os.path.join(self.config['output']['dir'], 
                                self.config['output']['graph_dir']), 
                    exist_ok=True)
    
    def connect_to_database(self):
        """Establish connection to MySQL database"""
        try:
            self.conn = mysql.connector.connect(
                host=self.config['database']['host'],
                user=self.config['database']['user'],
                password=self.config['database']['password'],
                database=self.config['database']['database']
            )
            self.cursor = self.conn.cursor(dictionary=True)
            print("Connected to database successfully")
            return True
        except mysql.connector.Error as err:
            print(f"Database connection failed: {err}")
            return False
    
    def fetch_utility_usage_data(self):
        """Fetch utility usage data from database"""
        query = """
        SELECT 
            DATE(u._usage_time_) as usage_date,
            CASE 
                WHEN g._gas_id_ IS NOT NULL THEN 'Gas'
                WHEN e._electricity_id_ IS NOT NULL THEN 'Electricity'
                WHEN w._water_id_ IS NOT NULL THEN 'Water'
            END as utility_type,
            SUM(u._usage_amount_) as total_usage
        FROM usage_table u
        JOIN iot_table i ON u._iot_id_ = i._iot_id_
        JOIN utility_table ut ON i._utility_id_ = ut._utility_id_
        LEFT JOIN gas_table g ON ut._utility_id_ = g._gas_id_
        LEFT JOIN water_table w ON ut._utility_id_ = w._water_id_
        LEFT JOIN electricity_table e ON ut._utility_id_ = e._electricity_id_
        WHERE u._usage_time_ >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY usage_date, utility_type
        ORDER BY usage_date, utility_type
        """
        
        self.cursor.execute(query)
        result = self.cursor.fetchall()
        
        # Convert to pandas DataFrame for easier manipulation
        df = pd.DataFrame(result)
        self.data['utility_usage'] = df.to_dict('records')
        return df
    
    def fetch_active_users_data(self):
        """Fetch active users data"""
        query = """
        SELECT 
            DATE(l._log_time_) as login_date,
            COUNT(DISTINCT l._user_id_) as active_users
        FROM user_login_log_table l
        WHERE l._log_time_ >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY login_date
        ORDER BY login_date
        """
        
        self.cursor.execute(query)
        result = self.cursor.fetchall()
        
        df = pd.DataFrame(result)
        self.data['active_users'] = df.to_dict('records')
        return df
    
    def fetch_outage_data(self):
        """Fetch outage data"""
        query = """
        SELECT 
            DATE(o._start_time_) as outage_date,
            CASE 
                WHEN hi._high_impact_id_ IS NOT NULL THEN 'High'
                WHEN mi._medium_impact_id_ IS NOT NULL THEN 'Medium'
                WHEN li._low_impact_id_ IS NOT NULL THEN 'Low'
            END as impact_level,
            COUNT(*) as outage_count
        FROM outage_table o
        JOIN outage_mapping_table om ON o._outage_id_ = om._outage_id_
        LEFT JOIN high_impact_table hi ON om._outage_map_id_ = hi._high_impact_id_
        LEFT JOIN medium_impact_table mi ON om._outage_map_id_ = mi._medium_impact_id_
        LEFT JOIN low_impact_table li ON om._outage_map_id_ = li._low_impact_id_
        WHERE o._start_time_ >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY outage_date, impact_level
        ORDER BY outage_date, impact_level
        """
        
        self.cursor.execute(query)
        result = self.cursor.fetchall()
        
        df = pd.DataFrame(result)
        self.data['outages'] = df.to_dict('records')
        return df
    
    def fetch_payment_data(self):
        """Fetch payment transaction data"""
        query = """
        SELECT 
            DATE(r._recharge_time_) as payment_date,
            COUNT(*) as transaction_count,
            SUM(r._recharge_amount_) as total_amount
        FROM recharge_table r
        WHERE r._recharge_time_ >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY payment_date
        ORDER BY payment_date
        """
        
        self.cursor.execute(query)
        result = self.cursor.fetchall()
        
        df = pd.DataFrame(result)
        self.data['payments'] = df.to_dict('records')
        return df
    
    def fetch_all_data(self):
        """Fetch all required data from database"""
        if not hasattr(self, 'conn') or not self.conn.is_connected():
            if not self.connect_to_database():
                return False
                
        try:
            self.utility_df = self.fetch_utility_usage_data()
            self.active_users_df = self.fetch_active_users_data()
            self.outage_df = self.fetch_outage_data()
            self.payment_df = self.fetch_payment_data()
            
            # Additional stats
            self.fetch_additional_stats()
            
            return True
        except mysql.connector.Error as err:
            print(f"Error fetching data: {err}")
            return False
    
    def fetch_additional_stats(self):
        """Fetch additional statistics"""
        # Total registered users
        self.cursor.execute("SELECT COUNT(*) as total_users FROM user_table")
        total_users = self.cursor.fetchone()['total_users']
        
        # Total active IoT devices
        self.cursor.execute("SELECT COUNT(*) as active_devices FROM active_iot_table")
        active_devices = self.cursor.fetchone()['active_devices']
        
        # Total unpaid IoT devices
        self.cursor.execute("SELECT COUNT(*) as unpaid_devices FROM unpaid_iot_table")
        unpaid_devices = self.cursor.fetchone()['unpaid_devices']
        
        # Total pending IoT requests
        self.cursor.execute("SELECT COUNT(*) as pending_requests FROM pending_request_table")
        pending_requests = self.cursor.fetchone()['pending_requests']
        
        self.data['stats'] = {
            'total_users': total_users,
            'active_devices': active_devices,
            'unpaid_devices': unpaid_devices,
            'pending_requests': pending_requests
        }
    
    def generate_graphs(self):
        """Generate various graphs for analytics dashboard"""
        graphs_info = []
        
        # Set the style for all plots
        sns.set_style("whitegrid")
        plt.rcParams['figure.figsize'] = (12, 6)
        
        # 1. Utility Usage Over Time
        if not self.utility_df.empty:
            try:
                utility_pivot = self.utility_df.pivot_table(
                    values='total_usage', 
                    index='usage_date', 
                    columns='utility_type',
                    fill_value=0
                )
                
                # Plot
                plt.figure(figsize=(12, 6))
                plt.title('Utility Usage Over Time (Last 30 Days)', fontsize=16)
                plt.xlabel('Date', fontsize=12)
                plt.ylabel('Usage Amount', fontsize=12)
                
                for column in utility_pivot.columns:
                    plt.plot(utility_pivot.index, utility_pivot[column], marker='o', linestyle='-', label=column)
                
                plt.legend()
                plt.tight_layout()
                
                # Save
                graph_path = os.path.join(
                    self.config['output']['dir'],
                    self.config['output']['graph_dir'],
                    'utility_usage.png'
                )
                plt.savefig(graph_path)
                plt.close()
                
                graphs_info.append({
                    'title': 'Utility Usage Over Time',
                    'filename': 'utility_usage.png',
                    'description': 'Shows the usage trends for electricity, water, and gas over the last 30 days.'
                })
            except Exception as e:
                print(f"Error generating utility usage graph: {e}")
        
        # 2. Active Users
        if not self.active_users_df.empty:
            try:
                plt.figure(figsize=(12, 6))
                plt.title('Daily Active Users (Last 30 Days)', fontsize=16)
                plt.xlabel('Date', fontsize=12)
                plt.ylabel('Number of Active Users', fontsize=12)
                
                plt.plot(self.active_users_df['login_date'], self.active_users_df['active_users'], 
                        marker='o', linestyle='-', color='#3d81ff')
                
                plt.tight_layout()
                
                # Save
                graph_path = os.path.join(
                    self.config['output']['dir'],
                    self.config['output']['graph_dir'],
                    'active_users.png'
                )
                plt.savefig(graph_path)
                plt.close()
                
                graphs_info.append({
                    'title': 'Daily Active Users',
                    'filename': 'active_users.png',
                    'description': 'Shows the number of unique users logging in each day over the last 30 days.'
                })
            except Exception as e:
                print(f"Error generating active users graph: {e}")
        
        # 3. Outage Impact Distribution
        if not self.outage_df.empty:
            try:
                # Aggregate data
                impact_data = self.outage_df.groupby('impact_level')['outage_count'].sum().reset_index()
                
                # Define colors for impact levels
                colors = {'High': '#ff5959', 'Medium': '#ffb259', 'Low': '#53cf6b'}
                impact_colors = [colors.get(level, '#999999') for level in impact_data['impact_level']]
                
                plt.figure(figsize=(10, 6))
                plt.title('Outage Impact Distribution (Last 30 Days)', fontsize=16)
                
                plt.pie(impact_data['outage_count'], labels=impact_data['impact_level'], 
                       autopct='%1.1f%%', colors=impact_colors, startangle=90)
                plt.axis('equal')
                
                plt.tight_layout()
                
                # Save
                graph_path = os.path.join(
                    self.config['output']['dir'],
                    self.config['output']['graph_dir'],
                    'outage_impact.png'
                )
                plt.savefig(graph_path)
                plt.close()
                
                graphs_info.append({
                    'title': 'Outage Impact Distribution',
                    'filename': 'outage_impact.png',
                    'description': 'Shows the distribution of outages by impact level (High, Medium, Low).'
                })
            except Exception as e:
                print(f"Error generating outage impact graph: {e}")
        
        # 4. Payment Transactions
        if not self.payment_df.empty:
            try:
                fig, ax1 = plt.subplots(figsize=(12, 6))
                
                # Transaction count
                color = '#3d81ff'
                ax1.set_xlabel('Date', fontsize=12)
                ax1.set_ylabel('Transaction Count', color=color, fontsize=12)
                ax1.plot(self.payment_df['payment_date'], self.payment_df['transaction_count'], 
                        marker='o', linestyle='-', color=color)
                ax1.tick_params(axis='y', labelcolor=color)
                
                # Total amount on secondary y-axis
                ax2 = ax1.twinx()
                color = '#53cf6b'
                ax2.set_ylabel('Total Amount', color=color, fontsize=12)
                ax2.plot(self.payment_df['payment_date'], self.payment_df['total_amount'], 
                        marker='s', linestyle='--', color=color)
                ax2.tick_params(axis='y', labelcolor=color)
                
                plt.title('Payment Transactions (Last 30 Days)', fontsize=16)
                plt.tight_layout()
                
                # Save
                graph_path = os.path.join(
                    self.config['output']['dir'],
                    self.config['output']['graph_dir'],
                    'payment_transactions.png'
                )
                plt.savefig(graph_path)
                plt.close()
                
                graphs_info.append({
                    'title': 'Payment Transactions',
                    'filename': 'payment_transactions.png',
                    'description': 'Shows the number of payment transactions and total amount over the last 30 days.'
                })
            except Exception as e:
                print(f"Error generating payment transactions graph: {e}")
        
        self.data['graphs'] = graphs_info
        return graphs_info
    
    def generate_ai_summary(self):
        """Generate AI summary using LM Studio"""
        try:
            # Prepare prompt for the model
            stats = self.data['stats']
            
            utility_stats = {
                'electricity': sum(item['total_usage'] for item in self.data['utility_usage'] 
                            if item['utility_type'] == 'Electricity'),
                'water': sum(item['total_usage'] for item in self.data['utility_usage'] 
                            if item['utility_type'] == 'Water'),
                'gas': sum(item['total_usage'] for item in self.data['utility_usage'] 
                            if item['utility_type'] == 'Gas')
            }
            
            # Count recent activity trends
            if self.active_users_df.shape[0] >= 14:  # Need at least 14 days of data
                recent_active = self.active_users_df.iloc[-7:]['active_users'].mean()
                previous_active = self.active_users_df.iloc[-14:-7]['active_users'].mean()
                active_trend = ((recent_active - previous_active) / previous_active) * 100 if previous_active > 0 else 0
            else:
                active_trend = 0
                
            # Payment trends
            if self.payment_df.shape[0] >= 14:  # Need at least 14 days of data
                recent_payments = self.payment_df.iloc[-7:]['total_amount'].sum()
                previous_payments = self.payment_df.iloc[-14:-7]['total_amount'].sum()
                payment_trend = ((recent_payments - previous_payments) / previous_payments) * 100 if previous_payments > 0 else 0
            else:
                payment_trend = 0
            
            prompt = f"""
            Analyze the following CLIX platform data and provide a concise analytics summary:
            
            User Statistics:
            - Total registered users: {stats['total_users']}
            - Active IoT devices: {stats['active_devices']}
            - Unpaid IoT devices: {stats['unpaid_devices']}
            - Pending IoT requests: {stats['pending_requests']}
            
            30-Day Utility Usage:
            - Electricity: {utility_stats['electricity']:.2f} kWh
            - Water: {utility_stats['water']:.2f} L
            - Gas: {utility_stats['gas']:.2f} m³
            
            Activity Trends:
            - User activity trend: {active_trend:.1f}% {'increase' if active_trend >= 0 else 'decrease'} compared to previous week
            - Payment trend: {payment_trend:.1f}% {'increase' if payment_trend >= 0 else 'decrease'} compared to previous week
            
            Please provide:
            1. A title for the analytics report
            2. A brief executive summary (2-3 sentences)
            3. Key insights (3-4 bullet points)
            4. Recommendations (2-3 bullet points)
            
            Format the response as JSON with the following structure:
            {{
                "title": "Analytics Report Title",
                "summary": "Executive summary text",
                "insights": ["Insight 1", "Insight 2", "Insight 3"],
                "recommendations": ["Recommendation 1", "Recommendation 2"]
            }}
            """
            
            # Send request to LM Studio API
            response = requests.post(
                f"{self.config['lm_studio']['api_base']}/chat/completions",
                json={
                    "model": self.config['lm_studio']['model'],
                    "messages": [
                        {"role": "system", "content": "You are a data analyst for CLIX, a utility management platform. Your task is to analyze platform data and provide concise, actionable insights formatted as requested."},
                        {"role": "user", "content": prompt}
                    ],
                    "temperature": 0.3,
                    "max_tokens": 1024
                }
            )
            
            if response.status_code == 200:
                result = response.json()
                ai_content = result['choices'][0]['message']['content']
                
                # Extract the JSON part from the response
                try:
                    import re
                    json_match = re.search(r'({.*})', ai_content, re.DOTALL)
                    if json_match:
                        ai_json = json.loads(json_match.group(1))
                    else:
                        ai_json = json.loads(ai_content)
                        
                    self.summary = ai_json
                    return ai_json
                except json.JSONDecodeError:
                    # If JSON parsing fails, return a formatted version
                    print("Failed to parse JSON from AI response, generating fallback summary")
                    lines = ai_content.strip().split('\n')
                    
                    # Simple extraction based on headings
                    title = "CLIX Analytics Report"
                    summary = ""
                    insights = []
                    recommendations = []
                    
                    current_section = None
                    for line in lines:
                        line = line.strip()
                        if not line:
                            continue
                            
                        if "title" in line.lower() or "report" in line.lower():
                            title = line.split(":", 1)[1].strip() if ":" in line else line
                            current_section = "title"
                        elif "summary" in line.lower() or "executive" in line.lower():
                            current_section = "summary"
                        elif "insight" in line.lower() or "key" in line.lower():
                            current_section = "insights"
                        elif "recommend" in line.lower():
                            current_section = "recommendations"
                        elif current_section == "summary":
                            summary += line + " "
                        elif current_section == "insights" and line.startswith("-") or line.startswith("•"):
                            insights.append(line[1:].strip())
                        elif current_section == "recommendations" and line.startswith("-") or line.startswith("•"):
                            recommendations.append(line[1:].strip())
                    
                    fallback_summary = {
                        "title": title,
                        "summary": summary.strip(),
                        "insights": insights[:4],  # Limit to 4 insights
                        "recommendations": recommendations[:3]  # Limit to 3 recommendations
                    }
                    
                    self.summary = fallback_summary
                    return fallback_summary
            else:
                print(f"Error calling LM Studio API: {response.status_code} - {response.text}")
                # Generate fallback summary
                self.summary = {
                    "title": "CLIX Platform Analytics Overview",
                    "summary": f"The CLIX platform has {stats['total_users']} registered users with {stats['active_devices']} active IoT devices in operation. User activity has shown a {active_trend:.1f}% {'increase' if active_trend >= 0 else 'decrease'} over the past week.",
                    "insights": [
                        f"The platform currently has {stats['unpaid_devices']} unpaid IoT devices requiring attention.",
                        f"There are {stats['pending_requests']} pending IoT connection requests that need processing.",
                        f"Total utility usage shows {'increasing' if sum(utility_stats.values()) > 0 else 'decreasing'} trends over the past 30 days."
                    ],
                    "recommendations": [
                        f"Address the {stats['unpaid_devices']} unpaid IoT devices through targeted user notifications.",
                        "Process pending IoT requests to improve user satisfaction and platform growth.",
                        "Monitor electricity usage patterns for optimization opportunities."
                    ]
                }
                return self.summary
        except Exception as e:
            print(f"Error generating AI summary: {e}")
            # Return a basic summary if AI generation fails
            self.summary = {
                "title": "CLIX Platform Status Report",
                "summary": f"Platform analysis showing {stats['total_users']} users and {stats['active_devices']} active devices.",
                "insights": [
                    "Utility usage patterns require monitoring.",
                    "Unpaid devices need attention.",
                    "Pending requests should be processed."
                ],
                "recommendations": [
                    "Improve payment collection system.",
                    "Streamline device activation process."
                ]
            }
            return self.summary
    
    def save_output(self):
        """Save processed data and AI summary to output files"""
        try:
            data_path = os.path.join(
                self.config['output']['dir'],
                self.config['output']['data_file']
            )
            with open(data_path, 'w') as f:
                json.dump(self.data, f, indent=2, default=str)
                
            summary_path = os.path.join(
                self.config['output']['dir'],
                self.config['output']['summary_file']
            )
            with open(summary_path, 'w') as f:
                json.dump(self.summary, f, indent=2)
                
            print(f"Data saved to {data_path}")
            print(f"Summary saved to {summary_path}")
            return True
        except Exception as e:
            print(f"Error saving output: {e}")
            return False
    
    def close(self):
        """Close database connection"""
        if hasattr(self, 'conn') and self.conn.is_connected():
            self.cursor.close()
            self.conn.close()
            print("Database connection closed")
    
    def run_analytics(self):
        """Run the complete analytics pipeline"""
        try:
            print("Starting CLIX analytics pipeline...")
            
            # Step 1: Fetch data
            print("Fetching data from database...")
            if not self.fetch_all_data():
                return False
                
            # Step 2: Generate graphs
            print("Generating analytical graphs...")
            self.generate_graphs()
            
            # Step 3: Generate AI summary
            print("Generating AI-powered analytics summary...")
            self.generate_ai_summary()
            
            # Step 4: Save output
            print("Saving analytics output...")
            self.save_output()
            
            print("Analytics pipeline completed successfully!")
            return True
        except Exception as e:
            print(f"Error in analytics pipeline: {e}")
            return False
        finally:
            self.close()

if __name__ == "__main__":
    analytics = CLIXAnalytics()
    analytics.run_analytics()
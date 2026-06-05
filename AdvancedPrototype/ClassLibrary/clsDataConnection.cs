using System;
using System.Data;
using System.Data.SqlClient;
using System.Configuration;

namespace ClassLibrary
{
    public class clsDataConnection
    {
        private SqlConnection mConn;
        private SqlDataAdapter mDA;
        private DataSet mDS = new DataSet();
        private string mSQL;

        public clsDataConnection()
        {
            string connectionString = ConfigurationManager.ConnectionStrings["LibraryDB"].ConnectionString;
            mConn = new SqlConnection(connectionString);
        }

        public void SetSQL(string sql)
        {
            mSQL = sql;
            mDA = new SqlDataAdapter(mSQL, mConn);
        }

        public void AddParameter(string paramName, object paramValue)
        {
            mDA.SelectCommand.Parameters.AddWithValue(paramName, paramValue ?? DBNull.Value);
        }

        public Int32 Execute()
        {
            mDS = new DataSet();
            mDA.Fill(mDS);
            return mDS.Tables[0].Rows.Count;
        }

        public Int32 ExecuteNonQuery()
        {
            SqlCommand cmd = new SqlCommand(mSQL, mConn);
            foreach (SqlParameter p in mDA.SelectCommand.Parameters)
                cmd.Parameters.Add(new SqlParameter(p.ParameterName, p.Value));
            mConn.Open();
            Int32 rows = cmd.ExecuteNonQuery();
            mConn.Close();
            return rows;
        }

        public object GetField(string fieldName, Int32 rowIndex = 0)
        {
            return mDS.Tables[0].Rows[rowIndex][fieldName];
        }

        public DataTable GetDataTable()
        {
            return mDS.Tables[0];
        }
    }
}

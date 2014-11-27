"""\
This experiment harness is intended to probe basic functionality 
of the algebra_composer in utils/algebra_composer. 

The premise is that a query be decomposed into SPARQL algebra and
that algebra is traversed to identify leaf BGPs (basic graph 
patterns) which can be wholly rewritten to a remote Store which 
supports the capabilities required to execute that BGP. 

addServiceCapabilities() executes the logic to traverse the SPARQL
algebra, identify matching nodes and rewrite it into a ServiceGraphPattern.

rewriteQueryToCapabilityMap() takes an Rdflif.query object and prints
the resulting rewritten query string. 

evalPart() is an accumulator method in algebra_composer which conducts a
depth-first traversal of the algebra tree and composes a parseable
SPARQL query string. 
"""
from rdflib.plugins.sparql import prepareQuery
from rdflib.plugins.sparql import algebra
from rdflib.plugins.sparql.parserutils import CompValue, Expr
from utils.algebra_composer.composer import evalPart, groupGraphPatternPredicateTypes 
from rdflib import Literal, Variable, URIRef, BNode

# Dummy data for a map of server capabilities to known predicates. 
# In a live environment, these would be looked up in a backend Store
# supporting OWL Reasoning to enable implication, etc. 
capabilityPredicates = {
	'serverA' : 'http://foaf/'
}

# Dummy namespaces recognised by this script. 
# In a live environment, these would be ascertained from the backend
# Store referenced above. 
initNS = { "foaf": 'http://foaf/...' };


# Dummy query to be parsed and rewritten. 
# The expected behaviour is that this would be rewritten into a
# federated query where ?ddd is mapped to a remote service. 
q = prepareQuery(
       	"""SELECT (?dbp AS ?b) WHERE {  
       		?dbp a ?ddd. ?q a ?ddd .
       		FILTER (?ddd > 100 && ?ddd < 50)
       	}  

       	OFFSET 1 LIMIT 5""",
         initNs = initNS)


# Call the composer's addServiceCapabilities() on the query algebra
# and print the recomposed query
def rewriteQueryToCapabilityMap(q):
	# Note, for debugging, use: algebra.pprintAlgebra(<node?>) 

	# Navigate to BGP node in tree
	prologue_ = []
	for prefix, uri in q.prologue.namespace_manager.namespaces():
		prologue_.append("PREFIX %s: <%s>" % (prefix, uri,))

	capabilityServiceMappings = [ ('http://froogle.com/', algebra.Join( algebra.BGP([( Variable('a'), Variable('a'), Variable('a'))]), algebra.BGP([( Variable('b'), Variable('b'), Variable('b'))]))), ]

	addServiceCapabilities(q.algebra, capabilityServiceMappings)
	query_ = evalPart(q.prologue, q.algebra)

	
	#print "\n".join(prologue_)
	print query_
	#			ServiceGraphPattern(
    #                    term = http://fish.db.endpoint
    #                    graph = GroupGraphPatternSub(
    #                        part = [TriplesBlock_{'_vars': set([?dbp]), 'triples': [[?dbp, rdflib.term.URIRef(u'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'), rdflib.term.Literal(u'fish')]]}]
    #                        _vars = set([?dbp])
    #                        )
    #                    _vars = set([?dbp])
    #                    )
	try:
		prepareQuery("\n".join(prologue_) + query_, initNs = initNS)
	except:
		print '## Did not validate ##'

def addServiceCapabilities(part, capabilityMappings):
	if len(capabilityMappings) == 0:
		return part

	# Traverse till we get to the WHERE{} part of the algebra
	if part.p and part.p.name in groupGraphPatternPredicateTypes:
		(serviceEndpoint, graphPatternAlgebra) = capabilityMappings.pop()
		part.update(p=algebra.GraphJoin(addServiceCapabilities(part.p, capabilityMappings), graphPatternAlgebra, serviceEndpoint))
	else:
		addServiceCapabilities(part.p, capabilityMappings)



def ServiceGraphPattern(graphURI, subPattern):
    return CompValue('ServiceGraphPattern', term=graphURI, graph=subPattern)

def GroupGraphPatternSub(parts):
	return CompValue('GroupGraphPatternSub', part=parts)

def TriplesBlock(triples):
	return CompValue('TriplesBlock', triples=triples)

printAndValidateAlgebra(q)